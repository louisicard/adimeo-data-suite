<?php
/**
 * Created by PhpStorm.
 * User: louis
 * Date: 24/10/2018
 * Time: 11:58
 */

namespace App\Controller;


use AdimeoDataSuite\Bundle\ADSSecurityBundle\Security\Group;
use AdimeoDataSuite\Bundle\ADSSecurityBundle\Security\User;
use AdimeoDataSuite\Index\IndexManager;
use AdimeoDataSuite\Model\SecurityContext;
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

  /** @var SecurityContext */
  private $securityContext = NULL;

  /**
   * @return SecurityContext
   */
  protected function buildSecurityContext() {
    if($this->securityContext == null) {
      $context = new SecurityContext();
      $restrictions = array(
        'indexes' => [],
        'datasources' => [],
        'matchingLists' => [],
        'dictionaries' => [],
      );
      /** @var User $user */
      $user = $this->container->get('security.token_storage')->getToken()->getUser();
      $context->setIsAdmin(in_array('ROLE_ADMIN', $user->getRoles()));
      if ($user instanceof User) {
        $context->setUserUid($user->getUid());
        $groupNames = $user->getGroups();
        foreach ($groupNames as $groupName) {
          /** @var Group $group */
          $group = $this->getIndexManager()->findObject('group', $groupName);
          $restrictions['indexes'] += $group->getIndexes();
          $restrictions['datasources'] += $group->getDatasources();
          $restrictions['matchingLists'] += $group->getMatchingLists();
          $restrictions['dictionaries'] += $group->getDictionaries();
        }
      }
      $context->setRestrictions($restrictions);
      $this->securityContext = $context;
    }
    return $this->securityContext;
  }

}