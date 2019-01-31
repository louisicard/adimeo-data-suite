<?php

namespace App\Controller;


use AdimeoDataSuite\Datasource\WebCrawler;
use AdimeoDataSuite\Model\Datasource;
use AdimeoDataSuite\Model\DummyOutputManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DatasourceController extends AdimeoDataSuiteController
{

  public function listDatasourcesAction(Request $request) {
    $datasourceTypes = [];
    $ids = $this->container->getParameter('adimeodatasuite.datasources');
    foreach($ids as $id) {
      /** @var Datasource $ds */
      $ds = $this->container->get($id);
      $datasourceTypes[$ds->getDisplayName()] = get_class($ds);
    }
    $form = $this->createFormBuilder(null)
      ->add('dataSourceType', ChoiceType::class, array(
        'choices' => array($this->get('translator')->trans('Select') . ' >' => '') + $datasourceTypes,
        'required' => true,
      ))
      ->add('ok', SubmitType::class, array(
        'label' => $this->get('translator')->trans('Add')
      ))
      ->getForm();
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
      $data = $form->getData();
      return $this->redirect($this->generateUrl('datasource-add', array('datasourceType' => $data['dataSourceType'])));
    }

    return $this->render('datasource.html.twig', array(
      'title' => $this->get('translator')->trans('Data sources'),
      'main_menu_item' => 'datasources',
      'datasources' => $this->getIndexManager()->listObjects('datasource', $this->buildSecurityContext()),
      'form_add_datasource' => $form->createView(),
      'procs' => $this->getRunningDatasources()
    ));
  }

  public function addOrEditDatasourceAction(Request $request) {
    if ($request->get('datasourceType') != null) {
      $datasourceType = $request->get('datasourceType');

      /** @var Datasource $datasource */
      $datasource = new $datasourceType();
      $edit = false;

    } elseif($request->get('id') != null) {
      $datasource = $this->getIndexManager()->findObject('datasource', $request->get('id'), $this->buildSecurityContext());
      $edit = true;
    } else {
      $this->addSessionMessage('error', $this->get('translator')->trans('No datasource type provided'));
      return $this->redirect($this->generateUrl('datasources'));
    }
    $form = $this->createFormBuilder($edit ? $datasource->getSettings() + array('hasBatchExecution' => $datasource->hasBatchExecution()) : NULL);
    $form->add('name', TextType::class, array(
      'label' => $this->get('translator')->trans('Name'),
      'required' => true,
    ));
    $this->addControls($form, $datasource->getSettingFields(), $datasource);
    $form->add('hasBatchExecution', CheckboxType::class, array(
      'label' => $this->get('translator')->trans('Has batch execution'),
      'required' => false
    ));
    $form->add('submit', SubmitType::class, array(
      'label' => $this->get('translator')->trans($edit ? 'Update datasource' : 'Create datasource')
    ));
    $form = $form->getForm();
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
      $settings = $this->handleFileUpload($form, $datasource->getSettingFields());;
      $datasource->setHasBatchExecution($settings['hasBatchExecution']);
      unset($settings['hasBatchExecution']);
      $datasource->setSettings($settings);
      if(!$edit) {
        $datasource->setCreatedBy($this->container->get('security.token_storage')->getToken()->getUser()->getUid());
      }
      $this->getIndexManager()->persistObject($datasource);
      $this->addSessionMessage('status', $this->get('translator')->trans('Datasource has been added'));
      return $this->redirect($this->generateUrl('datasources'));
    }
    return $this->render('datasource.html.twig', array(
      'title' => $this->get('translator')->trans($edit ? 'Edit datasource' : 'New datasource'),
      'main_menu_item' => 'datasources',
      'form' => $form->createView()
    ));
  }

  public function executeDatasourceAction(Request $request) {
    if ($request->get('id') != null) {
      /** @var Datasource $instance */
      $instance = $this->getIndexManager()->findObject('datasource', $request->get('id'));
      $procs = $this->getRunningDatasources();
      if(isset($procs[$instance->getId()])){
        return $this->render('datasource.html.twig', array(
          'title' => $this->get('translator')->trans('Monitor "@ds_name"', array('@ds_name' => $instance->getName())),
          'main_menu_item' => 'datasources',
          'proc' => $procs[$instance->getId()],
          'datasource' => $instance
        ));
      }
      else {
        $form = $this->createFormBuilder();
        $this->addControls($form, $instance->getExecutionArgumentFields(), $instance);
        $form->add('submit', SubmitType::class, array(
          'label' => $this->get('translator')->trans('Execute')
        ));
        $form = $form->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
          $data = $this->handleFileUpload($form, $instance->getExecutionArgumentFields());
          $this->launchProcess($data, $instance->getId());
          $this->addSessionMessage('status', $this->get('translator')->trans('Datasource has been launched'));
          return $this->redirect($this->generateUrl('datasource-exec', array('id' => $instance->getId())));
        }
        return $this->render('datasource.html.twig', array(
          'title' => $this->get('translator')->trans('Execute "@ds_name"', array('@ds_name' => $instance->getName())),
          'main_menu_item' => 'datasources',
          'form' => $form->createView()
        ));
      }
    } else {
      $this->addSessionMessage('error', $this->get('translator')->trans('No id provided'));
      return $this->redirect($this->generateUrl('datasources'));
    }
  }

  private function launchProcess($args, $id){
    $paramsQsR = [];
    foreach($args as $k => $v){
      $paramsQsR[] = '"' . str_replace('"', '\\"', $v) . '"';
    }
    $paramsQs = implode(' ', $paramsQsR);
    $output = $this->getOutputFile($id);
    popen($this->getCommand($id) . ' ' . $paramsQs . ' > ' . $output . ' 2>&1 &', 'w');
  }

  private function getOutputFile($id){
    $fs = new Filesystem();
    if(!$fs->exists(__DIR__ . '/../../var')){
      $fs->mkdir(__DIR__ . '/../../var');
    }
    if(!$fs->exists(__DIR__ . '/../../var/outputs')){
      $fs->mkdir(__DIR__ . '/../../var/outputs');
    }
    return __DIR__ . '/../../var/outputs/' . $id;
  }

  private function getCommand($id){
    $bin = PHP_BINARY;
    if(!is_executable($bin)){
      $bin = PHP_BINDIR . '/php';
    }
    $console = __DIR__ . '/../../bin/console';
    $cmd = '"' . $bin . '" "' . $console . '" ads:exec ' . $id;
    return $cmd;
  }

  private function getOutputContent($id, $offset = 0){
    if(file_exists($this->getOutputFile($id)))
      $content = file_get_contents($this->getOutputFile($id), null, null, $offset);
    else
      $content = '';
    return $content;
  }

  private function getRunningDatasources(){
    $r = array();
    $procs = '';
    exec('ps aux | grep -i "ads:exec" | grep -v "grep"', $procs);
    exec('ps aux | grep -i "ads:oai" | grep -v "grep"', $procs);
    foreach($procs as $proc){
      $raw = preg_split('/[ ]+/', $proc);
      $info = array(
        'pid' => $raw[1],
        'owner' => $raw[0],
        'cpu' => $raw[2],
        'mem' => $raw[3],
        'time' => $raw[9],
      );
      if(isset($raw[13])){
        $info['id'] = $raw[13];
        $r[$raw[13]] = $info;
      }
    }
    return $r;
  }

  public function kill($id){
    $procs = $this->getRunningDatasources();
    if(isset($procs[$id])){
      $pid = $procs[$id]['pid'];
      exec('kill -9 ' . $pid);
    }
  }

  public function getDatasourceOutputAction(Request $request) {
    $output = $this->getOutputContent($request->get('id'), $request->get('from'));
    return new Response($output, 200, array('Content-Type' => 'text/plain; charset=utf-8'));
  }

  public function killDatasourceAction(Request $request) {
    if ($request->get('id') != null) {
      $this->kill($request->get('id'));
      $this->addSessionMessage('status', $this->get('translator')->trans('Datasource has been killed'));
    } else {
      $this->addSessionMessage('error', $this->get('translator')->trans('No id provided'));
    }
    return $this->redirect($this->generateUrl('datasources'));
  }

  public function deleteDatasourceAction(Request $request) {
    if ($request->get('id') != null) {
      $this->getIndexManager()->deleteObject($request->get('id'));
      $this->addSessionMessage('status', $this->get('translator')->trans('Datasource has been deleted'));
    } else {
      $this->addSessionMessage('error', $this->get('translator')->trans('No id provided'));
    }
    return $this->redirect($this->generateUrl('datasources'));
  }

  public function ajaxListDatasourcesAction(Request $request) {
    $datasources = $this->getIndexManager()->listObjects('datasource', $this->buildSecurityContext());
    $r = [];
    foreach($datasources as $datasource){
      /** @var Datasource $datasource */
      $r[] = array(
        'id' => $datasource->getId(),
        'name' => $datasource->getName(),
        'class' => get_class($datasource)
      );
    }
    return new Response(json_encode($r, JSON_PRETTY_PRINT), 200, array('Content-type' => 'application/json; charset=utf-8'));
  }

  public function webCrawlerResponseAction(Request $request) {
    if($request->get('datasourceId') != null){
      $datasource = $this->getIndexManager()->findObject('datasource', $request->get('datasourceId'));
      if($datasource instanceof WebCrawler) {
        $datasource->initForExecution($this->getIndexManager(), new DummyOutputManager(), $this->container->get('adimeo_data_suite_pdo_pool'));
        $datasource->handleDataFromCallback(array(
          'title' => $request->get('title') != null ? $request->get('title') : '',
          'html' => $request->get('html') != null ? $request->get('html') : '',
          'url' => $request->get('url') != null ? $request->get('url') : '',
        ));
        return new Response(json_encode(array('Status' => 'OK')), 200, array('Content-type' => 'text/html; charset=utf-8'));
      }
    }
    return new Response(json_encode(array('Error' => 'Provided datasource is incorrect')), 400, array('Content-type' => 'application/json'));
  }

}