<?php

namespace App\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EditionController extends AdimeoDataSuiteController {

  public function getEditRecordFormAction(Request $request)
  {
    $mapping = $request->get('mapping');
    $id = $request->get('id');

    $index = strpos($mapping, '.') !== 0 ? explode('.', $mapping)[0] : '.' . explode('.', $mapping)[1];
    $mappingName = strpos($mapping, '.') !== 0 ? explode('.', $mapping)[1] : explode('.', $mapping)[2];

    $mappingDef = $this->getIndexManager()->getMapping($index, $mappingName)['properties'];

    $res = $this->getIndexManager()->search($index, json_decode('{"query":{"ids":{"values":["' . $id . '"]}}}', TRUE));
    if(isset($res['hits']['hits'][0])) {
      $record = $res['hits']['hits'][0];
    }
    else {
      $record = NULL;
    }

    return new Response(json_encode(array('mapping' => $mappingDef, 'record' => $record), JSON_PRETTY_PRINT), 200, array('Content-type' => 'application/json; charset=utf-8'));
  }

  public function editRecordAction(Request $request)
  {
    $mapping = $request->get('mapping');
    $id = $request->get('id');
    $doc = json_decode($request->getContent(), TRUE);
    $doc['_id'] = $id;

    $index = strpos($mapping, '.') !== 0 ? explode('.', $mapping)[0] : '.' . explode('.', $mapping)[1];
    $mappingName = strpos($mapping, '.') !== 0 ? explode('.', $mapping)[1] : explode('.', $mapping)[2];

    $this->getIndexManager()->indexDocument($index, $mappingName, $doc);

    return new Response(json_encode(array('status' => 'ok'), JSON_PRETTY_PRINT), 200, array('Content-type' => 'application/json; charset=utf-8'));
  }

}
