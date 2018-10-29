<?php

namespace App\Controller;


use AdimeoDataSuite\Model\Datasource;
use AdimeoDataSuite\Model\Processor;
use AdimeoDataSuite\Model\ProcessorFilter;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProcessorController extends AdimeoDataSuiteController
{

  public function listProcessorsAction(Request $request)
  {

    $datasources = $this->getIndexManager()->listObjects('datasource');
    $indexes = $this->getIndexManager()->getIndicesInfo();
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
    $processors = $this->getIndexManager()->listObjects('processor');
    $listForDisplay = [];
    foreach($processors as $processor) {
      $listForDisplay[] = array(
        'id' => $processor->getId(),
        'datasource_name' => $this->getIndexManager()->findObject('datasource', $processor->getDatasourceId())->getName(),
        'target' => $processor->getTarget()
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
    } else { //Edit
      $processor = $this->getIndexManager()->findObject('processor', $id);
      $datasource = $this->getIndexManager()->findObject('datasource', $processor->getDatasourceId());
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
        'label' => $this->get('translator')->trans('JSON Definition'),
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
      $this->getIndexManager()->persistObject($proc);
      if ($id == null) {
        $this->addSessionMessage('status', $this->get('translator')->trans('New processor has been added successfully'));
      } else {
        $this->addSessionMessage('status', $this->get('translator')->trans('Processor has been updated successfully'));
      }
      if ($id == null)
        return $this->redirect($this->generateUrl('processors'));
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
      foreach($filter->getSettingFields() as $key => $field) {
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
        if(isset($field['default']) && !isset($data['setting_' . $key])) {
          $params['data'] = $field['default'];
        }
        if($field['type'] == 'boolean') {
          $params['multiple'] = true;
        }
        if(isset($field['trim'])) {
          $params['trim'] = $field['trim'];
        }
        $form->add('setting_' . $key, $controlType, $params);
      }
      $form->add('submit', SubmitType::class, array(
        'label' => $this->get('translator')->trans('OK')
      ));
      $form = $form->getForm();
      $form->handleRequest($request);
      if ($form->isSubmitted() && $form->isValid()) {
        $data = $form->getData();
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

}