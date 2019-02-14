<?php

namespace App\Controller;


use AdimeoDataSuite\Bundle\ADSSecurityBundle\Security\Group;
use AdimeoDataSuite\Bundle\ADSSecurityBundle\Security\User;
use AdimeoDataSuite\Index\BackupsManager;
use AdimeoDataSuite\Index\IndexManager;
use AdimeoDataSuite\Index\StatIndexManager;
use AdimeoDataSuite\Model\Datasource;
use AdimeoDataSuite\Model\PersistentObject;
use AdimeoDataSuite\Model\SecurityContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AdimeoDataSuiteController extends Controller
{

  /**
   * @return IndexManager
   */
  protected function getIndexManager() {
    return $this->container->get('adimeo_data_suite_es_server');
  }

  /**
   * @return StatIndexManager
   */
  protected function getStatIndexManager() {
    return $this->container->get('adimeo_data_suite_stat_es_server');
  }

  /**
   * @return BackupsManager
   */
  protected function getBackupsManager() {
    return $this->container->get('adimeo_data_suite_backups_es_server');
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

  protected function addControls(FormBuilderInterface $formBuilder, $fields, PersistentObject $object = null, $fieldPrefix = '') {
    foreach($fields as $key => $field) {
      $controlType = null;
      $params = array(
        'label' => $field['label'],
        'required' => $field['required']
      );
      switch($field['type']) {
        case 'string':
          $controlType = TextType::class;
          break;
        case 'integer':
          $controlType = IntegerType::class;
          break;
        case 'textarea':
          $controlType = TextareaType::class;
          break;
        case 'boolean':
          $controlType = CheckboxType::class;
          break;
        case 'file':
          $controlType = FileType::class;
          break;
        case 'choice':
          $controlType = ChoiceType::class;
          if(isset($field['multiple']))
            $params['multiple'] = $field['multiple'];
          if(isset($field['choices']))
            $params['choices'] = $field['choices'];
          if(isset($field['bound_to'])) {
            $choices = array('Select >' => '');
            if($field['bound_to'] == 'index') {
              $indexes = $this->getIndexManager()->getIndicesInfo($this->buildSecurityContext());
              foreach($indexes as $indexName => $info) {
                $choices[$indexName] = $indexName;
              }
            }
            elseif($field['bound_to'] == 'mapping') {
              $indexes = $this->getIndexManager()->getIndicesInfo($this->buildSecurityContext());
              $targetChoices = array();
              foreach ($indexes as $indexName => $info) {
                $subChoices = array();
                if (isset($info['mappings'])) {
                  foreach ($info['mappings'] as $mapping) {
                    $subChoices[$indexName . '.' . $mapping['name']] = $indexName . '.' . $mapping['name'];
                  }
                }
                $targetChoices[$indexName] = $subChoices;
              }
              ksort($targetChoices);
              $choices += $targetChoices;
            }
            else {
              $objects = $this->getIndexManager()->listObjects($field['bound_to'], $this->buildSecurityContext());
              foreach ($objects as $object) {
                $choices[$object->getName()] = $object->getId();
              }
            }
            $params['choices'] = $choices;
          }
          break;
      }
      if(isset($field['trim'])) {
        $params['trim'] = $field['trim'];
      }
      if(isset($field['default_from_settings']) && $field['default_from_settings']) {
        if($object != null && $object instanceof Datasource)
        $params['data'] = $object->getSettings()[$key];
      }
      $formBuilder->add($fieldPrefix . $key, $controlType, $params);
    }
  }

  protected function handleFileUpload(FormInterface $form, $fields) {
    $data = $form->getData();
    foreach($fields as $k => $field) {
      if($field['type'] == 'file') {
        $value = $data[$k];
        if($value instanceof UploadedFile) {
          $fs = new Filesystem();
          if(!$fs->exists(__DIR__ . '/../../var')){
            $fs->mkdir(__DIR__ . '/../../var');
          }
          if(!$fs->exists(__DIR__ . '/../../var/outputs')){
            $fs->mkdir(__DIR__ . '/../../var/outputs');
          }
          $path = $value->move(__DIR__ . '/../../var/uploads');
          $data[$k] = $path;
        }
      }
    }
    return $data;
  }

}