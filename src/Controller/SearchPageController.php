<?php

namespace App\Controller;


use AdimeoDataSuite\Model\SearchPage;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchPageController extends AdimeoDataSuiteController
{

  public function listSearchPagesAction(Request $request) {
    $searchPages = $this->getIndexManager()->listObjects('search_page', $this->buildSecurityContext());
    return $this->render('search-page.html.twig', array(
      'title' => $this->get('translator')->trans('Search pages'),
      'main_menu_item' => 'search-pages',
      'searchPages' => $searchPages
    ));
  }

  public function addSearchPageAction(Request $request) {
    return $this->handleAddOrEditSearchPage($request);
  }

  public function editSearchPageAction(Request $request) {
    return $this->handleAddOrEditSearchPage($request, $request->get('id'));
  }

  public function deleteSearchPageAction(Request $request) {
    if ($request->get('id') != null) {
      $this->getIndexManager()->deleteObject($request->get('id'));
      $this->addSessionMessage('status', $this->get('translator')->trans('Search page has been deleted'));
    } else {
      $this->addSessionMessage('error', $this->get('translator')->trans('No id provided'));
    }
    return $this->redirect($this->generateUrl('search-pages'));
  }

  public function getMappingFields(Request $request, $mapping){

    $r = array();
    if(count(explode('.', $mapping)) > 1){
      $indexName = strpos($mapping, '.') === 0 ? ('.' . explode('.', $mapping)[1]) : explode('.', $mapping)[0];
      $mappingName = strpos($mapping, '.') === 0 ? explode('.', $mapping)[2] : explode('.', $mapping)[1];
      $type = $this->getIndexManager()->getMapping($indexName, $mappingName);
      if($type != null){
        foreach($type['properties'] as $field_name => $field){
          $r = array_merge($r, $this->getFieldDefitions($field, $field_name));
        }
      }
    }

    return new Response(json_encode($r, JSON_PRETTY_PRINT), 200, array('Content-type' => 'application/json; charset=utf-8'));
  }

  private function getFieldDefitions($field, $parent)
  {
    $r = array();
    if(isset($field['type']) && isset($field['properties']) && $field['type'] == 'nested'){
      foreach($field['properties'] as $field_name => $child){
        $r = array_merge($r, $this->getFieldDefitions($child, $parent . '.' . $field_name));
      }
    }
    else{
      $r[] = $parent;
      if(isset($field['fields'])){
        foreach($field['fields'] as $field_name => $child){
          $r = array_merge($r, $this->getFieldDefitions($child, $parent . '.' . $field_name));
        }
      }
    }
    return $r;
  }

  private function handleAddOrEditSearchPage(Request $request, $id = null) {
    if ($id == null) { //Add
      $searchPage = new SearchPage('', '', '{}');
    } else { //Edit
      $searchPage = $this->getIndexManager()->findObject('search_page', $request->get('id'));
    }
    $info = $this->getIndexManager()->getIndicesInfo($this->buildSecurityContext());
    $mappingChoices = array();
    foreach ($info as $k => $data) {
      if(isset($data['mappings'])) {
        foreach ($data['mappings'] as $mapping) {
          $mappingChoices[$k . '.' . $mapping['name']] = $k . '.' . $mapping['name'];
        }
      }
    }
    asort($mappingChoices);
    $mappingChoices = array_merge(array(
      $this->get('translator')->trans('Select a mapping') => ''
    ), $mappingChoices);
    $form = $this->createFormBuilder($searchPage)
      ->add('id', HiddenType::class)
      ->add('name', TextType::class, array(
        'label' => $this->get('translator')->trans('Search page name'),
        'required' => true,
      ))
      ->add('mapping', ChoiceType::class, array(
        'label' => $this->get('translator')->trans('Mapping'),
        'choices' => $mappingChoices,
        'required' => true
      ))
      ->add('definition', TextareaType::class, array(
        'label' => $this->get('translator')->trans('JSON Definition'),
        'required' => true
      ))
      ->add('save', SubmitType::class, array('label' => $this->get('translator')->trans('Save')))
      ->getForm();
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
      if (json_decode($searchPage->getDefinition()) == null) {
        $this->addSessionMessage('error', $this->get('translator')->trans('JSON parsing failed.'));
      } else {
        if($id == null){
          $searchPage->setCreatedBy($this->container->get('security.token_storage')->getToken()->getUser()->getUid());
        }
        $this->getIndexManager()->persistObject($searchPage);
        if ($id == null) {
          $this->addSessionMessage('status', $this->get('translator')->trans('New search page has been added successfully'));
        } else {
          $this->addSessionMessage('status', $this->get('translator')->trans('Search page has been updated successfully'));
        }
        if ($id == null)
          return $this->redirect($this->generateUrl('search-pages'));
        else {
          return $this->redirect($this->generateUrl('search-page-edit', array('id' => $id)));
        }
      }
    }
    return $this->render('search-page.html.twig', array(
      'title' => $id == null ? $this->get('translator')->trans('New search page') : $this->get('translator')->trans('Edit search page'),
      'main_menu_item' => 'search-pages',
      'form' => $form->createView()
    ));
  }

  public function displaySearchPageAction(Request $request, $id)
  {
    /** @var SearchPage $searchPage */
    $searchPage = $this->getIndexManager()->findObject('search_page', $id);

    $params = array(
      'mapping' => $searchPage->getMapping(),
      'sp_id' => $id,
    );

    $url = $this->generateUrl('search_client_homepage', $params);

    return $this->redirect($url);
  }
}