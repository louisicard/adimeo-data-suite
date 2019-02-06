<?php

namespace App\Controller;


use AdimeoDataSuite\Model\Datasource;
use AdimeoDataSuite\Model\Processor;
use AdimeoDataSuite\Model\ProcessorFilter;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProcessorController extends AdimeoDataSuiteController
{

  public function listProcessorsAction(Request $request)
  {
    /** @var Datasource[] $datasources */
    $datasources = $this->getIndexManager()->listObjects('datasource', $this->buildSecurityContext());
    $indexes = $this->getIndexManager()->getIndicesInfo($this->buildSecurityContext());
    $datasourceChoices = array();
    foreach ($datasources as $datasource) {
      $datasourceChoices[$datasource->getName()] = $datasource->getId();
    }
    ksort($datasourceChoices);
    $targetChoices = array();
    foreach ($indexes as $indexName => $info) {
      $choices = array();
      if (isset($info['mappings'])) {
        foreach ($info['mappings'] as $mapping) {
          $choices[$indexName . '.' . $mapping['name']] = $indexName . '.' . $mapping['name'];
        }
      }
      $targetChoices[$indexName] = $choices;
    }
    ksort($targetChoices);
    $form = $this->createFormBuilder(null)
      ->add('datasource', ChoiceType::class, array(
        'choices' => array($this->get('translator')->trans('Select datasource') => '') + $datasourceChoices,
        'required' => true,
      ))
      ->add('target', ChoiceType::class, array(
        'choices' => array($this->get('translator')->trans('Select a target') => '') + $targetChoices,
        'required' => true,
      ))
      ->add('ok', SubmitType::class, array(
        'label' => $this->get('translator')->trans('Add')
      ))
      ->getForm();
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
      $data = $form->getData();
      return $this->redirect($this->generateUrl('processor-add', array('datasource' => $data['datasource'], 'target' => $data['target'])));
    }
    /** @var Processor[] $processors */
    $processors = $this->getIndexManager()->listObjects('processor', $this->buildSecurityContext());
    $listForDisplay = [];
    foreach($processors as $processor) {
      $datasource = $this->getIndexManager()->findObject('datasource', $processor->getDatasourceId());
      $listForDisplay[] = array(
        'id' => $processor->getId(),
        'datasource_name' => $datasource != null ? $datasource->getName() : '!!! Missing datasource',
        'target' => $processor->getTarget(),
        'delete_only' => $datasource == null
      );
    }
    return $this->render('processor.html.twig', array(
      'title' => $this->get('translator')->trans('Processors'),
      'main_menu_item' => 'processors',
      'processors' => $listForDisplay,
      'form_add_processor' => $form->createView()
    ));
  }

  public function addProcessorAction(Request $request)
  {
    if ($request->get('datasource') != null && $request->get('target') != null) {
      return $this->handleAddOrEditProcessor($request);
    } else {
      $this->addSessionMessage('error', $this->get('translator')->trans('No datasource or target provided'));
      return $this->redirect($this->generateUrl('processors'));
    }
  }

  public function editProcessorAction(Request $request)
  {
    if ($request->get('id')) {
      return $this->handleAddOrEditProcessor($request, $request->get('id'));
    } else {
      $this->addSessionMessage('error', $this->get('translator')->trans('No ID provided'));
      return $this->redirect($this->generateUrl('processors'));
    }
  }

  private function handleAddOrEditProcessor($request, $id = null)
  {
    if ($id == null) { //Add
      /** @var Datasource $datasource */
      $datasource = $this->getIndexManager()->findObject('datasource', $request->get('datasource'));
      $target = $request->get('target');
      $definition = array(
        'datasource' => array(
          'id' => $request->get('datasource'),
          'name' => $datasource->getName(),
          'fields' => $datasource->getOutputFields(),
        ),
        'filters' => array(),
        'target' => $target,
      );
      $processor = new Processor();
      $processor->setDatasourceId($request->get('datasource'));
      $processor->setTarget($request->get('target'));
      $processor->setDefinition(json_encode($definition, JSON_PRETTY_PRINT));
      $edit = false;
    } else { //Edit
      $processor = $this->getIndexManager()->findObject('processor', $id);
      $datasource = $this->getIndexManager()->findObject('datasource', $processor->getDatasourceId());
      $edit = true;
    }
    if(is_array($processor->getTargetSiblings())){
      $processor->setTargetSiblings(implode(',', $processor->getTargetSiblings()));
    }
    $form = $this->createFormBuilder($processor)
      ->add('datasourceName', TextType::class, array(
        'label' => $this->get('translator')->trans('Datasource'),
        'data' => $datasource->getName(),
        'disabled' => true,
        'required' => true,
        'mapped' => false
      ))
      ->add('target', TextType::class, array(
        'label' => $this->get('translator')->trans('Target'),
        'disabled' => true,
        'required' => true
      ))
      ->add('targetSiblings', HiddenType::class, array())
      ->add('definition', TextareaType::class, array(
        'label' => $this->get('translator')->trans('Processor chain'),
        'required' => true
      ))
      ->add('save', SubmitType::class, array('label' => $this->get('translator')->trans('Save')))
      ->getForm();
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
      /** @var Processor $proc */
      $proc = $form->getData();
      if($proc->getTargetSiblings() != ''){
        $proc->setTargetSiblings(explode(',', $proc->getTargetSiblings()));
      }
      if(!$edit) {
        $proc->setCreatedBy($this->container->get('security.token_storage')->getToken()->getUser()->getUid());
      }
      $this->getIndexManager()->persistObject($proc);
      if ($id == null) {
        $this->addSessionMessage('status', $this->get('translator')->trans('New processor has been added successfully'));
      } else {
        $this->addSessionMessage('status', $this->get('translator')->trans('Processor has been updated successfully'));
      }
      return $this->redirect($this->generateUrl('processor-edit', array('id' => $proc->getId())));
    }
    $target_r = explode('.', $processor->getTarget());
    $indexName = $target_r[0];
    $mappingName = $target_r[1];
    $mapping = $this->getIndexManager()->getMapping($indexName, $mappingName);
    if ($mapping != null)
      $targetFields = array_keys($mapping['properties']);
    else
      $targetFields = array();
    $filterTypes = [];
    $ids = $this->container->getParameter('adimeodatasuite.filters');
    foreach($ids as $id) {
      /** @var ProcessorFilter $filter */
      $filter = $this->container->get($id);
      $filterTypes[str_replace("\\", '\\\\', get_class($filter))] = $filter->getDisplayName();
    }
    asort($filterTypes);
    return $this->render('processor.html.twig', array(
      'title' => $id == null ? $this->get('translator')->trans('New processor') : $this->get('translator')->trans('Edit processor'),
      'main_menu_item' => 'processors',
      'filterTypes' => $filterTypes,
      'form' => $form->createView(),
      'targetFields' => $targetFields,
      'mappingName' => $mappingName,
      'datasourceId' => $processor->getDatasourceId(),
      'datasourceFields' => $datasource->getOutputFields(),
    ));
  }

  public function deleteProcessorAction(Request $request)
  {
    if ($request->get('id') != null) {
      $this->getIndexManager()->deleteObject($request->get('id'));
      $this->addSessionMessage('status', $this->get('translator')->trans('Processor has been deleted'));
    } else {
      $this->addSessionMessage('error', $this->get('translator')->trans('No id provided'));
    }
    return $this->redirect($this->generateUrl('processors'));
  }

  public function getSettingsFormAction(Request $request)
  {
    if ($request->get('class') != null) {
      $class = $request->get('class');
      $data = $request->get('data') != null ? json_decode($request->get('data'), true) : array();
      /** @var ProcessorFilter $filter */
      $filter = new $class($data);
      $form = $this->createFormBuilder($filter->getArgumentsAndSettings())
        ->add('in_stack_name', TextType::class, array(
          'required' => true,
          'label' => $this->get('translator')->trans('Display name')
        ))
        ->add('autoImplode', CheckboxType::class, array(
          'required' => false,
          'label' => $this->get('translator')->trans('Auto-implode')
        ))
        ->add('autoImplodeSeparator', TextType::class, array(
          'required' => false,
          'trim' => false,
          'label' => $this->get('translator')->trans('Auto-implode separator')
        ))
        ->add('autoStriptags', CheckboxType::class, array(
          'required' => false,
          'label' => $this->get('translator')->trans('Auto-striptags')
        ))
        ->add('isHTML', CheckboxType::class, array(
          'required' => false,
          'label' => $this->get('translator')->trans('Input is HTML')
        ));
      foreach ($filter->getArguments() as $k => $arg) {
        $form->add('arg_' . $k, TextType::class, array(
          'label' => $arg,
          'required' => true,
          'attr' => array(
            'class' => 'filter-argument',
          )
        ));
      }
      $this->addControls($form, $filter->getSettingFields(), null, 'setting_');
      $form->add('submit', SubmitType::class, array(
        'label' => $this->get('translator')->trans('OK')
      ));
      $form = $form->getForm();
      $form->handleRequest($request);
      if ($form->isSubmitted() && $form->isValid()) {
        $data = $this->handleFileUpload($form, $filter->getSettingFields());
        $filter->setData($data);
        $response = array(
          'class' => $class,
          'filterDisplayName' => $filter->getDisplayName(),
          'settings' => $filter->getSettings(),
          'arguments' => $filter->getArgumentsData(),
          'inStackName' => $filter->getInStackName(),
          'autoImplode' => $filter->getAutoImplode(),
          'autoImplodeSeparator' => $filter->getAutoImplodeSeparator(),
          'autoStriptags' => $filter->getAutoStriptags(),
          'isHTML' => $filter->getIsHTML(),
          'fields' => $filter->getFields(),
        );
        return new Response(json_encode($response), 200, array('Content-type' => 'application/json'));
      }
      return $this->render('ajaxform.html.twig', array(
        'form' => $form->createView()
      ));
    }
  }

  public function exportProcessorAction(Request $request)
  {
    if ($request->get('id')) {
      /** @var Processor $proc */
      $proc = $this->getIndexManager()->findObject('processor', $request->get('id'));
      if ($proc != null) {
        return new Response($proc->export($this->getIndexManager()), 200, array('Content-type' => 'application/json;charset=utf-8', 'Content-disposition' => 'attachment;filename=processor_' . $proc->getTarget() . '.json'));
      } else {
        $this->addSessionMessage('error', $this->get('translator')->trans('No processor found for this id'));
        return $this->redirect($this->generateUrl('processors'));
      }
    } else {
      $this->addSessionMessage('error', $this->get('translator')->trans('No ID provided'));
      return $this->redirect($this->generateUrl('processors'));
    }
  }

  public function importProcessorAction(Request $request)
  {
    $form = $this->createFormBuilder()
      ->add('file', FileType::class, array(
        'label' => $this->get('translator')->trans('File'),
        'required' => true,
      ))
      ->add('override', CheckboxType::class, array(
        'label' => $this->get('translator')->trans('Override existing Index/Mapping'),
        'required' => false
      ))
      ->add('import', SubmitType::class, array('label' => $this->get('translator')->trans('Import')))
      ->getForm();
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
      $file = $form->getData()['file'];
      /* @var UploadedFile $file */
      $json = file_get_contents($file->getRealPath());
      $override = $form->getData()['override'];
      Processor::import($json, $this->getIndexManager(), $override);
      $this->addSessionMessage('status', $this->get('translator')->trans('Processor has been imported'));
      return $this->redirect($this->generateUrl('processor-import'));
    }
    return $this->render('processor.html.twig', array(
      'title' => $this->get('translator')->trans('Import'),
      'main_menu_item' => 'processors',
      'import_form' => $form->createView(),
    ));
  }

}