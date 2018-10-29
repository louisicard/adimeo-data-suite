<?php
/**
 * Created by PhpStorm.
 * User: louis
 * Date: 24/10/2018
 * Time: 16:13
 */

namespace App\Controller;


use Elasticsearch\Common\Exceptions\NoNodesAvailableException;
use Symfony\Component\HttpFoundation\Request;

class DashboardController extends AdimeoDataSuiteController
{

  public function indexAction(Request $request) {
    try {

      $info = $this->getIndexManager()->getIndicesInfo($this->buildSecurityContext());

      $serverInfo = $this->getIndexManager()->getServerInfo()['server_info'];

    } catch (NoNodesAvailableException $ex) {
      $info = null;
      $serverInfo = null;
      $noMenu = true;
    }

    return $this->render('dashboard.html.twig', array(
      'title' => $this->get('translator')->trans('Welcome to Adimeo Data Suite'),
      'info' => $info,
      'server_info' => $serverInfo,
      'main_menu_item' => 'home',
      'no_menu' => isset($noMenu) && $noMenu ? true : false,
    ));
  }

}