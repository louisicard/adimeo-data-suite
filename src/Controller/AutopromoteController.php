<?php

namespace App\Controller;


use AdimeoDataSuite\Model\Autopromote;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AutopromoteController extends AdimeoDataSuiteController
{

  public function listAutopromotesAction(Request $request) {
    $autopromotes = $this->getIndexManager()->listAutopromotes($this->buildSecurityContext());
    return $this->render('autopromote.html.twig', array(
      'title' => $this->get('translator')->trans('Auto-promotes'),
      'main_menu_item' => 'autopromotes',
      'autopromotes' => $autopromotes
    ));
  }

  public function addAutopromoteAction(Request $request) {
    return $this->handleAddOrEditAutopromote($request);
  }

  public function editAutopromoteAction(Request $request) {
    return $this->handleAddOrEditAutopromote($request, $request->get('id'));
  }

  public function deleteAutopromoteAction(Request $request) {
    if ($request->get('id') != null && $request->get('index') != null) {
      $this->getIndexManager()->deleteAutopromote($request->get('id'), $request->get('index'));
      $this->addSessionMessage('status', $this->get('translator')->trans('Autopromote has been deleted'));
    } else {
      $this->addSessionMessage('error', $this->get('translator')->trans('No id and/or index provided'));
    }
    return $this->redirect($this->generateUrl('autopromotes'));
  }


  private function handleAddOrEditAutopromote($request) {
    $id = $request->get('id');
    if ($id == null) { //Add
      $autopromote = new Autopromote('', '', '', '', '', '', '', '');
    } else { //Edit
      /** @var Autopromote $autopromote */
      $autopromote = $this->getIndexManager()->getAutopromote($id, $request->get('index'));
    }

    $indexes = array_keys($this->getIndexManager()->getIndicesInfo($this->buildSecurityContext()));
    asort($indexes);
    $indexChoices = array(
      'Select >' => '',
    );
    $analyzerChoices = array(
      'Please select an index first' => '',
    );
    foreach($indexes as $index){
      $indexChoices[$index] = $index;
    }
    $formBuilder = $this->createFormBuilder($autopromote, array('csrf_protection' => false))
      ->add('name', TextType::class, array(
        'label' => $this->get('translator')->trans('Title'),
        'required' => true,
      ))
      ->add('url', TextType::class, array(
        'label' => $this->get('translator')->trans('URL'),
        'required' => true,
      ))
      ->add('image', TextType::class, array(
        'label' => $this->get('translator')->trans('Image'),
        'required' => false,
      ))
      ->add('body', TextareaType::class, array(
        'label' => $this->get('translator')->trans('Body (HTML tags arre allowed)'),
        'required' => true,
      ))
      ->add('keywords', TextareaType::class, array(
        'label' => $this->get('translator')->trans('Keywords'),
        'required' => true,
      ))
      ->add('index', ChoiceType::class, array(
        'label' => $this->get('translator')->trans('Index'),
        'required' => true,
        'choices' => $indexChoices,
        'disabled' => $id != null
      ));
    if($id == null) {
      $formBuilder
        ->add('analyzer', ChoiceType::class, array(
          'label' => $this->get('translator')->trans('Analyzer'),
          'required' => true,
          'choices' => $analyzerChoices
        ))
        ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
          $obj = $event->getData();
          $analyzerChoices = array(
            'Select >' => '',
          );
          $analyzers = $this->getAnalyzersForIndex($obj['index']);
          foreach ($analyzers as $a) {
            $analyzerChoices[$a] = $a;
          }
          $event->getForm()->add('analyzer', ChoiceType::class, array(
            'label' => $this->get('translator')->trans('Analyzer'),
            'required' => true,
            'choices' => $analyzerChoices
          ));
        });
    }
    $formBuilder
      ->add('save', SubmitType::class, array('label' => $this->get('translator')->trans('Save')));
    $form = $formBuilder->getForm();
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
      $autopromoteIndex = $this->getIndexManager()->getIndex($this->getIndexManager()->getAutopromoteIndexName($autopromote->getIndex()));
      if($autopromoteIndex == NULL) {
        $this->getIndexManager()->createAutopromoteIndex($autopromote->getIndex(), $autopromote->getAnalyzer());
      }
      $this->getIndexManager()->saveAutopromote($autopromote);
      if ($id == null) {
        $this->addSessionMessage('status', $this->get('translator')->trans('New auto-promote has been added successfully'));
      } else {
        $this->addSessionMessage('status', $this->get('translator')->trans('Auto-promote has been updated successfully'));
      }
      return $this->redirect($this->generateUrl('autopromotes'));
    }
    return $this->render('autopromote.html.twig', array(
      'title' => $id == null ? $this->get('translator')->trans('New auto-promote') : $this->get('translator')->trans('Edit auto-promote'),
      'main_menu_item' => 'autopromotes',
      'form' => $form->createView()
    ));
  }

  public function getAnalyzersAction(Request $request) {
    $indexName = $request->get('index');
    $analyzers = $this->getAnalyzersForIndex($indexName);
    $index = $this->getIndexManager()->getIndex($this->getIndexManager()->getAutopromoteIndexName($indexName));
    $exists = $index != NULL;
    $r = array(
      'enabled' => !$exists,
      'value' => $exists ? $this->getIndexManager()->getAutopromoteAnalyzer($indexName) : NULL,
      'analyzers' => $analyzers
    );
    return new Response(json_encode($r), 200, array('Content-Type' => 'application/json; charset=utf-8'));
  }

  private function getAnalyzersForIndex($index){
    return $this->getIndexManager()->getAnalyzers($index);
  }

}