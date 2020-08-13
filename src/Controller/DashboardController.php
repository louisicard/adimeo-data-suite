<?php

namespace App\Controller;


use AdimeoDataSuite\Exception\ServerClientException;
use Symfony\Component\HttpFoundation\Request;

class DashboardController extends AdimeoDataSuiteController
{

  public function indexAction(Request $request) {
    try {

      $info = $this->getIndexManager()->getIndicesInfo($this->buildSecurityContext());

      $serverInfo = $this->getIndexManager()->getServerInfo()['server_info'];

    } catch (ServerClientException $ex) {
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