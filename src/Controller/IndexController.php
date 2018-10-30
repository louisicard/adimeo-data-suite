<?php

namespace App\Controller;

use AdimeoDataSuite\Exception\DictionariesPathNotDefinedException;
use AdimeoDataSuite\Index\SynonymsDictionariesManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use \Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\Translator;

class IndexController extends AdimeoDataSuiteController {

  public function listIndexesAction(Request $request) {
    $info = $this->getIndexManager()->getIndicesInfo($this->buildSecurityContext());
    ksort($info);

    return $this->render('indexes.html.twig', array(
        'title' => $this->get('translator')->trans('Indexes'),
        'main_menu_item' => 'indexes',
        'indexes' => $info,
    ));
  }

  public function addIndexAction(Request $request) {
    return $this->getIndexForm($request, true);
  }

  public function editIndexAction(Request $request) {
    if ($request->get('index_name') != null) {
      return $this->getIndexForm($request, false);
    } else {
      $this->addSessionMessage('error', $this->get('translator')->trans('No index provided'));
      return $this->redirect($this->generateUrl('indexes'));
    }
  }

  public function deleteIndexAction(Request $request) {
    if ($request->get('index_name') != null) {
      $this->getIndexManager()->deleteIndex($request->get('index_name'));
      $this->addSessionMessage('status', $this->get('translator')->trans('Index has been deleted'));
      return $this->redirect($this->generateUrl('indexes'));
    } else {
      $this->addSessionMessage( 'error', $this->get('translator')->trans('No index provided'));
      return $this->redirect($this->generateUrl('indexes'));
    }
  }

  public function editMappingAction(Request $request) {
    if ($request->get('index_name') != null && $request->get('mapping_name') != null) {
      return $this->getMappingForm($request, false);
    } else {
      $this->addSessionMessage('error', $this->get('translator')->trans('No index or mapping provided'));
      return $this->redirect($this->generateUrl('indexes'));
    }
  }

  public function addMappingAction(Request $request) {
    if ($request->get('index_name') != null) {
      return $this->getMappingForm($request, true);
    } else {
      $this->addSessionMessage('error', $this->get('translator')->trans('No index or mapping provided'));
      return $this->redirect($this->generateUrl('indexes'));
    }
  }

  private function getIndexForm($request, $add) {
    if ($add) {
      $index = [
        'indexName' => '',
        'settings' => '{}'
      ];
    } else {
      $indexSettings = $this->getIndexManager()->getIndex($request->get('index_name'));
      $index = array(
        'indexName' => array_keys($indexSettings)[0],
        'settings' => json_encode($indexSettings[array_keys($indexSettings)[0]]['settings']['index'], JSON_PRETTY_PRINT)
      );
    }
    $form = $this->createFormBuilder($index)
      ->add('indexName', TextType::class, array(
        'label' => $this->get('translator')->trans('Index name'),
        'disabled' => !$add,
        'required' => true
      ))
      ->add('settings', TextareaType::class, array(
        'label' => $this->get('translator')->trans('Settings (JSON syntax)'),
      ))
      ->add('create', SubmitType::class, array('label' => $add ? $this->get('translator')->trans('Create index') : $this->get('translator')->trans('Update index')))
      ->getForm();

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $index = $form->getData();
      try {
        if ($add) {
          $this->getIndexManager()->createIndex($index['indexName'], json_decode($index['settings'], TRUE));
          $this->addSessionMessage('status', $this->get('translator')->trans('Index has been created'));
        } else {
          $this->getIndexManager()->updateIndex($index['indexName'], json_decode($index['settings'], TRUE));
          $this->addSessionMessage('status', $this->get('translator')->trans('Index has been updated'));
        }
        return $this->redirect($this->generateUrl('indexes'));
      } catch (Exception $ex) {
        $this->addSessionMessage('error', $this->get('translator')->trans('An error as occured: ') . $ex->getMessage());
      }
    }

    return $this->render('indexes.html.twig', array(
        'title' => $add ? $this->get('translator')->trans('Add an index') : $this->get('translator')->trans('Edit index settings'),
        'main_menu_item' => 'indexes',
        'form' => $form->createView(),
    ));
  }

  private function getMappingForm($request, $add) {
    if ($add) {
      $mapping = array(
        'indexName' => $request->get('index_name'),
        'mappingName' => '',
        'wipeData' => false,
        'mappingDefinition' => '{}',
        'dynamicTemplates' => '',
      );
    } else {
      $mappingData = $this->getIndexManager()->getMapping($request->get('index_name'), $request->get('mapping_name'));
      $mapping = array(
        'indexName' => $request->get('index_name'),
        'mappingName' => $request->get('mapping_name'),
        'wipeData' => false,
        'mappingDefinition' => json_encode($mappingData['properties'], JSON_PRETTY_PRINT),
        'dynamicTemplates' => isset($mappingData['dynamic_templates']) ? json_encode($mappingData['dynamic_templates'], JSON_PRETTY_PRINT) : '',
      );
    }
    $analyzers = $this->getIndexManager()->getAnalyzers($request->get('index_name'));
    $fieldTypes = $this->getIndexManager()->getFieldTypes();
    $dateFormats = $this->getIndexManager()->getDateFormats();
    $form = $this->createFormBuilder($mapping)
      ->add('indexName', TextType::class, array(
        'label' => $this->get('translator')->trans('Index name'),
        'disabled' => true,
        'required' => true
      ))
      ->add('mappingName', TextType::class, array(
        'label' => $this->get('translator')->trans('Mapping name'),
        'disabled' => !$add,
        'required' => true
      ))
      ->add('wipeData', CheckboxType::class, array(
        'label' => $this->get('translator')->trans('Wipe data?'),
        'required' => false
      ))
      ->add('mappingDefinition', TextareaType::class, array(
        'label' => $this->get('translator')->trans('Mapping definition'),
        'required' => true
      ))
      ->add('dynamicTemplates', TextareaType::class, array(
        'label' => $this->get('translator')->trans('Dynamic templates'),
        'required' => false
      ))
      ->add('save', SubmitType::class, array('label' => $this->get('translator')->trans('Save mapping')))
      ->getForm();
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $mapping = $form->getData();
      if($mapping['dynamicTemplates'] == ''){
        $mapping['dynamicTemplates'] = NULL;
      }
      try {
        $this->getIndexManager()->putMapping(
          $mapping['indexName'],
          $mapping['mappingName'],
          json_decode($mapping['mappingDefinition'], true),
          $mapping['dynamicTemplates'] != NULL ? json_decode($mapping['dynamicTemplates'], true) : NULL,
          $mapping['wipeData']
        );
        $this->addSessionMessage('status', $this->get('translator')->trans('Mapping has been updated'));
        return $this->redirect($this->generateUrl('indexes'));
      } catch (Exception $ex) {
        $this->addSessionMessage('error', $this->get('translator')->trans('An error as occured: ') . $ex->getMessage());
      }
    }
    $vars = array(
      'title' => $this->get('translator')->trans('Edit mapping'),
      'main_menu_item' => 'indexes',
      'form' => $form->createView(),
      'analyzers' => $analyzers,
      'fieldTypes' => $fieldTypes,
      'dateFormats' => $dateFormats,
      'serverVersion' => $this->getIndexManager()->getServerMajorVersionNumber()
    );
    return $this->render('indexes.html.twig', $vars);
  }

  /**
   * @Route("/test-service", name="test-service")
   */
  public function testServiceAction(Request $request) {
    $data = array(
      'op' => 'test',
      'domain' => 'core-techs.fr'
    );
    $r = $this->getRestData('http://localhost:8080/CtSearchWebCrawler/service', $data);
    return new \Symfony\Component\HttpFoundation\Response(json_encode($r), 200, array('Content-type' => 'text/html'));
  }

  private function getRestData($url, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'data=' . urlencode(json_encode($data)));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $r = curl_exec($ch);
    curl_close($ch);
    return json_decode($r, true);
  }

  public function mappingStatAction(Request $request, $index_name, $mapping_name) {
    $mapping = $this->getIndexManager()->getMapping($index_name, $mapping_name);
    $data = array(
      'docs' => 0,
      'fields' => 0
    );
    if ($mapping != null) {
      $res = $this->getIndexManager()->search($index_name, '{"query":{"match_all":{"boost":1}}}', 0, 0, $mapping_name);
      if (isset($res['hits']['total']) && $res['hits']['total'] > 0) {
        $data['docs'] = $res['hits']['total'];
      }
      $data['fields'] = count(array_keys($mapping['properties']));
    }
    return new Response(json_encode($data), 200, array('Content-type' => 'application/json'));
  }

  public function listSynonymsDictionariesAction(Request $request){
    $vars = array(
      'title' => $this->get('translator')->trans('Synonyms'),
      'main_menu_item' => 'indexes'
    );

    /** @var SynonymsDictionariesManager $sdManager */
    $sdManager = $this->container->get('adimeo_data_suite_synonyms_dictionaries_manager');
    try {
      $vars['dictionaries'] = $sdManager->getDictionaries($this->buildSecurityContext());
    }
    catch(DictionariesPathNotDefinedException $ex) {
      $this->addSessionMessage('error', $this->get('translator')->trans('Synonyms dictionaries path is not set properly'));
    }

    return $this->render('synonyms.html.twig', $vars);
  }

  public function addOrEditSynonymsDictionariesAction(Request $request, $fileName = null){
    $vars = array(
      'title' => $this->get('translator')->trans('Synonyms'),
      'sub_title' => $fileName == null ? $this->get('translator')->trans('New dictionary') : $this->get('translator')->trans('Edit dictionary'),
      'main_menu_item' => 'indexes'
    );

    /** @var SynonymsDictionariesManager $sdManager */
    $sdManager = $this->container->get('adimeo_data_suite_synonyms_dictionaries_manager');
    try {
      $data = array(
        'name' => $fileName != null ? $fileName : '',
        'content' => $fileName != null ? file_get_contents($sdManager->getDictionariesPath() . DIRECTORY_SEPARATOR . $fileName) : '# Blank lines and lines starting with pound are comments.

# Explicit mappings match any token sequence on the LHS of "=>"
# and replace with all alternatives on the RHS.  These types of mappings
# ignore the expand parameter in the schema.
# Examples:
i-pod, i pod => ipod,
sea biscuit, sea biscit => seabiscuit

# Equivalent synonyms may be separated with commas and give
# no explicit mapping.  In this case the mapping behavior will
# be taken from the expand parameter in the schema.  This allows
# the same synonym file to be used in different synonym handling strategies.
# Examples:
ipod, i-pod, i pod
foozball , foosball
universe , cosmos

# If expand==true, "ipod, i-pod, i pod" is equivalent
# to the explicit mapping:
ipod, i-pod, i pod => ipod, i-pod, i pod
# If expand==false, "ipod, i-pod, i pod" is equivalent
# to the explicit mapping:
ipod, i-pod, i pod => ipod

# Multiple synonym mapping entries are merged.
foo => foo bar
foo => baz
# is equivalent to
foo => foo bar, baz',
      );
      $form = $this->createFormBuilder($data)
        ->add('name', TextType::class, array(
          'label' => $this->get('translator')->trans('Name'),
          'required' => true,
        ))
        ->add('content', TextareaType::class, array(
          'label' => $this->get('translator')->trans('Content'),
          'required' => true,
        ))
        ->add('submit', SubmitType::class, array(
          'label' => $this->get('translator')->trans('Save')
        ))
        ->getForm();

      $form->handleRequest($request);

      $info = pathinfo($fileName);

      if ($form->isSubmitted() && $form->isValid()) {
        $name = $form->getData()['name'];
        $name = str_replace('.txt', '', $name);
        $name = preg_replace('/\W/i', '_', strtolower($name));
        $file = $sdManager->getDictionariesPath() . DIRECTORY_SEPARATOR . $name . '.txt';
        $translator = $this->get('translator');
        if (!file_exists($file) || $info['filename'] == $name) {
          file_put_contents($file, $form->getData()['content']);
          $this->addSessionMessage('status', $translator->trans('File <strong>@path</strong> has been updated', array('@path' => realpath($file))));
          if ($fileName != null && $info['filename'] != $name) {
            unlink($sdManager->getDictionariesPath() . DIRECTORY_SEPARATOR . $fileName);
          }
          return $this->redirectToRoute('synonyms-list');
        } else {
          $this->addSessionMessage('error', $translator->trans('File <strong>@path</strong> already exists', array('@path' => realpath($file))));
        }
      }
    }
    catch(DictionariesPathNotDefinedException $ex) {
      $this->addSessionMessage('error', $this->get('translator')->trans('Synonyms dictionaries path is not set properly'));
    }

    $vars['form'] = isset($form) ? $form->createView() : NULL;

    return $this->render('synonyms.html.twig', $vars);
  }

  public function deleteSynonymsDictionariesAction(Request $request, $fileName){
    /** @var SynonymsDictionariesManager $sdManager */
    $sdManager = $this->container->get('adimeo_data_suite_synonyms_dictionaries_manager');
    try {
      $file = $sdManager->getDictionariesPath() . DIRECTORY_SEPARATOR . $fileName;
      unlink($file);
      $translator = $this->get('translator');
      $this->addSessionMessage('status', $translator->trans('File <strong>@path</strong> has been deleted', array('@path' => realpath($file))));
    }
    catch(DictionariesPathNotDefinedException $ex) {
      $this->addSessionMessage('error', $this->get('translator')->trans('Synonyms dictionaries path is not set properly'));
    }
    return $this->redirectToRoute('synonyms-list');
  }

}
