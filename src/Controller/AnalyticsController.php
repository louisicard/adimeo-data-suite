<?php

namespace App\Controller;


use AdimeoDataSuite\Stat\StatCompiler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AnalyticsController extends AdimeoDataSuiteController
{

  public function analyticsAction(Request $request) {

    $statChoices = [];
    $serviceIds = $this->container->getParameter("adimeodatasuite.stats");
    foreach ($serviceIds as $id) {
      $statChoices[get_class($this->container->get($id))] = $this->container->get($id)->getDisplayName();
    }

    $indexes = $this->getIndexManager()->getIndicesInfo();
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

    $params = array(
      'title' => $this->get('translator')->trans('Analytics'),
      'main_menu_item' => 'analytics',
      'statChoices' => $statChoices,
      'mappingChoices' => $targetChoices,
    );
    return $this->render('analytics.html.twig', $params);
  }

  public function compileAction(Request $request) {

    $class = $request->get('stat');
    $compiler = new $class($this->getStatIndexManager());
    /* @var StatCompiler $compiler */

    $from = \DateTime::createFromFormat('Y-m-d H:i:s', $request->get('date_from') . ' 00:00:00');
    $to = \DateTime::createFromFormat('Y-m-d H:i:s', $request->get('date_to') . ' 23:59:59');
    $period = $request->get('granularity');
    $mapping = $request->get('mapping');

    $compiler->compile($mapping, $from ? $from : null, $to ? $to : null, !empty($period) ? $period : StatCompiler::STAT_PERIOD_HOUR);

    $json = array(
      'headers' => $compiler->getHeaders(),
      'jsData' => $compiler->getJSData(),
      'data' => $compiler->getData(),
      'googleChartClass' => $compiler->getGoogleChartClass()
    );

    return new Response(json_encode($json, JSON_PRETTY_PRINT), 200, array('Content-type' => 'application/json;charset=utf-8'));
  }

}