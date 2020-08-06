<?php

namespace App\Controller;


use AdimeoDataSuite\Model\BoostQuery;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;

class BoostQueryController extends AdimeoDataSuiteController
{

  public function listBoostQueriesAction(Request $request) {
    /** @var BoostQuery $boostQueries */
    $boostQueries = $this->getIndexManager()->listObjects('boost_query', $this->buildSecurityContext());
    return $this->render('boost-query.html.twig', array(
      'title' => $this->get('translator')->trans('Boost queries'),
      'main_menu_item' => 'boost-queries',
      'boostQueries' => $boostQueries
    ));
  }

  public function addBoostQueryAction(Request $request) {
    return $this->handleAddOrEditBoostQuery($request);
  }

  public function editBoostQueryAction(Request $request) {
    return $this->handleAddOrEditBoostQuery($request, $request->get('id'));
  }

  public function deleteBoostQueryAction(Request $request) {
    if ($request->get('id') != null) {
      $this->getIndexManager()->deleteObject($request->get('id'));
      $this->addSessionMessage('status', $this->get('translator')->trans('Boost query has been deleted'));
    } else {
      $this->addSessionMessage('error', $this->get('translator')->trans('No id provided'));
    }
    return $this->redirect($this->generateUrl('boost-queries'));
  }


  private function handleAddOrEditBoostQuery(Request $request, $id = null) {
    if ($id == null) { //Add
      $boostQuery = new BoostQuery('', '', '');
    } else { //Edit
      /** @var BoostQuery $boostQuery */
      $boostQuery = $this->getIndexManager()->findObject('boost_query', $request->get('id'));
    }
    $info = $this->getIndexManager()->getIndicesList($this->buildSecurityContext());
    $mappingChoices = array(
      'Select >' => ''
    );
    foreach($info as $index => $data){
      if($this->getIndexManager()->isLegacy()) {
        foreach ($data['mappings'] as $mappingName => $mapping) {
          $mappingChoices[$index . '.' . $mappingName] = $index . '.' . $mappingName;
        }
      }
      else {
        $mappingChoices[$index] = $index . '._doc';
      }
    }
    $form = $this->createFormBuilder($boostQuery)
      ->add('target', ChoiceType::class, array(
        'label' => $this->get('translator')->trans('Target'),
        'required' => true,
        'choices' => $mappingChoices
      ))
      ->add('definition', TextareaType::class, array(
        'label' => $this->get('translator')->trans('Definition'),
        'required' => true,
      ))
      ->add('save', SubmitType::class, array('label' => $this->get('translator')->trans('Save')))
      ->getForm();
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
      $json = json_decode($form->getData()->getDefinition());
      if($json == null){
        $this->addSessionMessage('error', $this->get('translator')->trans('Definition must be valid JSON'));
      }
      else {
        $indexName = substr($boostQuery->getTarget(), 0, strpos($boostQuery->getTarget(), '.', 1));

        $testQuery = array(
          'query' => json_decode($boostQuery->getDefinition(), true)
        );
        try{
          $this->getIndexManager()->search($indexName, $testQuery);
          $testOk = true;
        }
        catch(\Exception $ex){
          $testOk = false;
        }

        if(!$testOk){
          $this->addSessionMessage('error', $this->get('translator')->trans('Server refused your query : ' . $ex->getMessage()));
        }
        else {
          $boostQuery->setDefinition(json_encode($json, JSON_PRETTY_PRINT));
          if($id == null) {
            $boostQuery->setCreatedBy($this->container->get('security.token_storage')->getToken()->getUser()->getUid());
          }
          $this->getIndexManager()->persistObject($boostQuery);
          if ($id == null) {
            $this->addSessionMessage('status', $this->get('translator')->trans('New boost query has been added successfully'));
          } else {
            $this->addSessionMessage('status', $this->get('translator')->trans('Boost query has been updated successfully'));
          }
          return $this->redirect($this->generateUrl('boost-queries'));
        }
      }
    }
    return $this->render('boost-query.html.twig', array(
      'title' => $id == null ? $this->get('translator')->trans('New boost query') : $this->get('translator')->trans('Edit boost query'),
      'main_menu_item' => 'boost-queries',
      'form' => $form->createView()
    ));
  }
}