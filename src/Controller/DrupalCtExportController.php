<?php

namespace App\Controller;

use AdimeoDataSuite\Datasource\DrupalCtExport;
use AdimeoDataSuite\Model\Datasource;
use AdimeoDataSuite\Model\DummyOutputManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DrupalCtExportController extends AdimeoDataSuiteController
{

  public function indexAction(Request $request) {
    $xml = $request->get('xml');
    $id = $request->get('id');
    $item_id = $request->get('item_id');
    $target_mapping = $request->get('target_mapping');
    if ($id == null || empty($id)) {
      return new Response('{"error":"Missing id parameter"}', 400, array('Content-type' => 'application/json;charset=utf-8'));
    } else {
      /** @var Datasource $datasource */
      $datasource = $this->getIndexManager()->findObject('datasource', $id);
      if($datasource == null || !$datasource instanceof DrupalCtExport){
        return new Response('{"error":"No Drupal datasource found for this id"}', 400, array('Content-type' => 'application/json;charset=utf-8'));
      }
      else{

        $method = $request->getMethod();
        switch ($method) {
          case 'PUT':
            if ($xml == null || empty($xml)) {
              return new Response('{"error":"Missing xml parameter"}', 400, array('Content-type' => 'application/json;charset=utf-8'));
            }
            $datasource->initForExecution($this->getIndexManager(), new DummyOutputManager(), $this->container->get('adimeo_data_suite_pdo_pool'));
            $datasource->execute(array('xml' => $xml));
            break;
          case 'DELETE':
            if ($item_id == null || empty($item_id)) {
              return new Response('{"error":"Missing item_id parameter"}', 400, array('Content-type' => 'application/json;charset=utf-8'));
            }
            if ($target_mapping == null || empty($target_mapping)) {
              return new Response('{"error":"Missing target_mapping parameter"}', 400, array('Content-type' => 'application/json;charset=utf-8'));
            }

            $indexName = strpos($target_mapping, '.') === 0 ? ('.' . explode('.', $target_mapping)[1]) : explode('.', $target_mapping)[0];
            $mappingName = strpos($target_mapping, '.') === 0 ? ('.' . explode('.', $target_mapping)[2]) : explode('.', $target_mapping)[1];
            $this->getIndexManager()->deleteByQuery($indexName, $mappingName, json_decode('{"query":{"ids":{"values":["' . $item_id . '"]}}}', true));

            break;
        }

        return new Response('{"success":"OK"}', 200, array('Content-type' => 'application/json;charset=utf-8'));
      }
    }
  }

}