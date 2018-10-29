<?php

namespace App\Controller;


use AdimeoDataSuite\Model\Datasource;
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
        'choices' => array($this->get('translator')->trans('Add a new datasource') => '') + $datasourceTypes,
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
      'datasources' => $this->getIndexManager()->listObjects('datasource'),
      'form_add_datasource' => $form->createView(),
      'procs' => []
    ));
  }

  public function addOrEditDatasourceAction(Request $request) {
    if ($request->get('datasourceType') != null) {
      $datasourceType = $request->get('datasourceType');

      /** @var Datasource $datasource */
      $datasource = new $datasourceType();
      $edit = false;

    } elseif($request->get('id') != null) {
      $datasource = $this->getIndexManager()->findObject('datasource', $request->get('id'));
      $edit = true;
    } else {
      $this->addSessionMessage('error', $this->get('translator')->trans('No datasource type provided'));
      return $this->redirect($this->generateUrl('datasources'));
    }
    $form = $this->createFormBuilder($edit ? $datasource->getSettings() : NULL);
    $form->add('name', TextType::class, array(
      'label' => $this->get('translator')->trans('Name'),
      'required' => true,
    ));
    foreach($datasource->getSettingFields() as $key => $field) {
      $controlType = null;
      switch($field['type']) {
        case 'string':
          $controlType = TextType::class;
          break;
        case 'integer':
          $controlType = IntegerType::class;
          break;
        case 'textarea':
          $controlType = TextareaType::class;
          break;
        case 'boolean':
          $controlType = ChoiceType::class;
          break;
      }
      $params = array(
        'label' => $field['label'],
        'required' => $field['required']
      );
      if(isset($field['default']) && !isset($datasource->getSettings()[$key])) {
        $params['data'] = $field['default'];
      }
      if($field['type'] == 'boolean') {
        $params['multiple'] = true;
      }
      $form->add($key, $controlType, $params);
    }
    $form->add('submit', SubmitType::class, array(
      'label' => $this->get('translator')->trans($edit ? 'Update datasource' : 'Create datasource')
    ));
    $form = $form->getForm();
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
      $datasource->setSettings($form->getData());
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
    $datasources = $this->getIndexManager()->listObjects('datasource');
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

}