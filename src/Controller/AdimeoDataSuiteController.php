<?php
/**
 * Created by PhpStorm.
 * User: louis
 * Date: 24/10/2018
 * Time: 11:58
 */

namespace App\Controller;


use AdimeoDataSuite\Index\IndexManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AdimeoDataSuiteController extends Controller
{

  /**
   * @return IndexManager
   */
  protected function getIndexManager() {
    return $this->container->get('adimeo_data_suite_es_server');
  }

  protected function addSessionMessage($type, $message) {
    if ($this->get('session')->get('messages') != null) {
      $messages = $this->get('session')->get('messages');
      $messages[] = array(
        'type' => is_object($message) || is_array($message) ? 'object' : $type,
        'text' => is_object($message) || is_array($message) ? \Krumo::dump($message, KRUMO_RETURN) : $message,
      );
    } else {
      $messages = array(
        array(
          'type' => is_object($message) || is_array($message) ? 'object' : $type,
          'text' => is_object($message) || is_array($message) ? \Krumo::dump($message, KRUMO_RETURN) : $message,
        )
      );
    }
    $this->get('session')->set('messages', $messages);
  }

}