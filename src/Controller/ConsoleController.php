<?php

namespace App\Controller;


use AdimeoDataSuite\Model\SavedQuery;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Request;

  class ConsoleController extends AdimeoDataSuiteController
{

  public function consoleAction(Request $request) {
    $indexes = $this->getIndexManager()->getIndicesList($this->buildSecurityContext());
    $targetChoices = array();
    foreach ($indexes as $indexName => $info) {
      if($this->getIndexManager()->isLegacy()) {
        $choices = array();
        if (isset($info['mappings'])) {
          foreach ($info['mappings'] as $mappingName => $mapping) {
            $choices[$indexName . '.' . $mappingName] = $indexName . '.' . $mappingName;
          }
        }
        $targetChoices[$indexName] = $choices;
      }
      else {
        $targetChoices[$indexName] = $indexName . '._doc';
      }
    }
    $listener = function(FormEvent $event) {
      $data = $event->getData();
      $data["searchQuery"] = json_encode(json_decode($data["searchQuery"]), JSON_PRETTY_PRINT);
      $event->setData($data);
    };
    if($request->get('id') != null){
      /** @var SavedQuery $savedQuery */
      $savedQuery = $this->getIndexManager()->findObject('saved_query', $request->get('id'));
    }
    else {
      $savedQuery = null;
    }
    $values = array(
      'mapping' => $savedQuery != null ? $savedQuery->getTarget() : '',
      'searchQuery' => $savedQuery != null && $savedQuery->getDefinition() != null ? $savedQuery->getDefinition() : json_encode(array('query' => array('match_all' => array("boost" => 1))), JSON_PRETTY_PRINT),
      'deleteByQuery' => false,
    );
    $form = $this->createFormBuilder($values)
      ->add('mapping', ChoiceType::class, array(
        'label' => $this->get('translator')->trans('Target'),
        'choices' => array($this->get('translator')->trans('Select a target') => '') + $targetChoices,
        'required' => true,
      ))
      ->add('searchQuery', TextareaType::class, array(
        'label' => $this->get('translator')->trans('Search query (JSON)'),
        'required' => true
      ))
      ->add('deleteByQuery', CheckboxType::class, array(
        'label' => $this->get('translator')->trans('Delete records matching this query'),
        'required' => false
      ))
      ->add('execute', SubmitType::class, array('label' => $this->get('translator')->trans('Execute')))
      ->addEventListener(FormEvents::PRE_SUBMIT, $listener)
      ->getForm();

    $form->handleRequest($request);
    $params = array(
      'title' => $this->get('translator')->trans('Console'),
      'form' => $form->createView(),
      'main_menu_item' => 'console',
    );
    if ($form->isSubmitted() && $form->isValid()) {
      $data = $form->getData();
      $query_r = json_decode($data['searchQuery'], true);
      $index = strpos($data['mapping'], '.') !== 0 ? explode('.', $data['mapping'])[0] : '.' . explode('.', $data['mapping'])[1];
      $mapping = strpos($data['mapping'], '.') !== 0 ? explode('.', $data['mapping'])[1] : explode('.', $data['mapping'])[2];
      try {
        if (!$data['deleteByQuery']) {
          $res = $this->getIndexManager()->search($index, $query_r, isset($query_r['from']) ? $query_r['from'] : 0, isset($query_r['size']) ? $query_r['size'] : 20, $mapping);
          $params['results'] = $this->dumpVar($res);
          if(isset($res['aggregations']) && count($res['aggregations']) > 0){
            $params['facets'] = json_encode($res['aggregations'], JSON_PRETTY_PRINT);
          }
          $params['engine_response'] = $this->getFormattedEngineReponse($res);
          $saveUrlParams = array();
          if($request->get('id') != null){
            $saveUrlParams['id'] = $request->get('id');
          }
          $saveUrlParams['target'] = $data['mapping'];
          $saveUrlParams['query'] = $data['searchQuery'];
          $saveUrl = $this->generateUrl('console-save', $saveUrlParams);
          $params['save_url'] = $saveUrl;
          if(isset($saveUrlParams['id'])){
            unset($saveUrlParams['id']);
            $params['clone_url'] = $this->generateUrl('console-save', $saveUrlParams);
          }
        } else {
          $this->getIndexManager()->deleteByQuery($index, $query_r, $mapping);
        }
      } catch (\Exception $ex) {
        $params['exception'] = $ex->getMessage() . ', Line ' . $ex->getLine() . ' in ' . $ex->getFile();
      }
    }
    return $this->render('console.html.twig', $params);
  }

  public function saveQueryAction(Request $request) {
    $id = $request->get('id');
    $target = $request->get('target');
    $query = $request->get('query');
    $savedQuery = new SavedQuery();
    $savedQuery->setId($id);
    $savedQuery->setTarget($target);
    $savedQuery->setDefinition($query);
    $savedQuery->setCreatedBy($this->container->get('security.token_storage')->getToken()->getUser()->getUid());
    $r = $this->getIndexManager()->persistObject($savedQuery);
    $this->addSessionMessage('status', $this->get('translator')->trans('Your query has been saved'));
    return $this->redirectToRoute('console', array('id' => $r->getId()));
  }

  public function loadQueryAction(Request $request) {
    $params = array(
      'title' => $this->get('translator')->trans('Console'),
      'main_menu_item' => 'console',
    );
    $list = $this->getIndexManager()->listObjects('saved_query', $this->buildSecurityContext());
    $params['list'] = $list;
    return $this->render('console.html.twig', $params);
  }

  public function deleteQueryAction(Request $request) {
    $id = $request->get('id');
    $this->getIndexManager()->deleteObject($id);
    $this->addSessionMessage('status', $this->get('translator')->trans('Your query has been deleted'));
    return $this->redirectToRoute('console-load');
  }

  private function getFormattedEngineReponse($res){
    $r = array();
    if(isset($res['hits']['total'])){
      $r['total'] = $res['hits']['total'];
    }
    else{
      $r['total'] = 0;
    }
    $r['cols'] = array();
    if(isset($res['hits']['hits'])){
      foreach($res['hits']['hits'] as $index => $hit){
        if(isset($hit['_source'])){
          foreach(array_keys($hit['_source']) as $k){
            if(!in_array($k, $r['cols'])){
              $r['cols'][] = $k;
            }
            if(is_array($hit['_source'][$k])){
              $res['hits']['hits'][$index]['_source'][$k] = $this->dumpVar($res['hits']['hits'][$index]['_source'][$k]);
            }
          }
        }
      }
      $r['hits'] = $res['hits']['hits'];
    }
    else{
      $r['hits'] = array();
    }
    asort($r['cols']);
    return $r;
  }

  private function dumpVar($var) {
    if (is_object($var)) {
      $var = (array) $var;
    }
    if (is_array($var)) {
      $html = '<ul class="ctsearch-dump">';
      foreach ($var as $k => $v) {
        $html .= '<li>' . $k . ' (' . gettype($v) . ')' . ' => ' . $this->dumpVar($v) . '</li>';
      }
      $html .= '</ul>';
    } else {
      $html = $var;
    }
    return $html;
  }

}