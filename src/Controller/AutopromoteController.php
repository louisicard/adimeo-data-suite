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

class AutopromoteController extends AdimeoDataSuiteController
{

  public function listAutopromotesAction(Request $request) {
    $autopromotes = $this->getIndexManager()->listObjects('autopromote', $this->buildSecurityContext());
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
    if ($request->get('id') != null) {
      /** @var Autopromote $autopromote */
      $autopromote = $this->getIndexManager()->findObject('autopromote', $request->get('id'));
      if($autopromote != null) {
        $this->getIndexManager()->deleteObject($autopromote->getId());
      }
      $this->addSessionMessage('status', $this->get('translator')->trans('Autopromote has been deleted'));
    } else {
      $this->addSessionMessage('error', $this->get('translator')->trans('No id provided'));
    }
    return $this->redirect($this->generateUrl('autopromotes'));
  }


  private function handleAddOrEditAutopromote($request, $id = null) {
    if ($id == null) { //Add
      $autopromote = new Autopromote('', '', '', '', '', '', '', '');
    } else { //Edit
      /** @var Autopromote $autopromote */
      $autopromote = $this->getIndexManager()->findObject('autopromote', $id);
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
      ->add('title', TextType::class, array(
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
      if($id == null) {
        $autopromote->setCreatedBy($this->container->get('security.token_storage')->getToken()->getUser()->getUid());
      }
      $this->getIndexManager()->persistObject($autopromote);

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
    $analyzers = $this->getAnalyzersForIndex($request->get('index'));
    $exists = $this->getIndexManager()->mappingExists($request->get('index'), 'ctsearch_autopromote');
    $r = array(
      'enabled' => !$exists,
      'value' => $exists ? $this->getIndexManager()->getAutopromoteAnalyzer($request->get('index')) : NULL,
      'analyzers' => $analyzers
    );
    return new Response(json_encode($r), 200, array('Content-Type' => 'application/json; charset=utf-8'));
  }

  private function getAnalyzersForIndex($index){
    return $this->getIndexManager()->getAnalyzers($index);
  }

}