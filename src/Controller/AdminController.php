<?php

namespace App\Controller;

use AdimeoDataSuite\Bundle\ADSSecurityBundle\Security\Group;
use AdimeoDataSuite\Bundle\ADSSecurityBundle\Security\User;
use AdimeoDataSuite\Exception\DictionariesPathNotDefinedException;
use AdimeoDataSuite\Index\SynonymsDictionariesManager;
use AdimeoDataSuite\Model\Datasource;
use AdimeoDataSuite\Model\MatchingList;
use AdimeoDataSuite\Model\Parameter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends AdimeoDataSuiteController
{

  public function listUsersAction(Request $request) {
    $users = $this->getIndexManager()->listObjects('user');
    return $this->render('user.html.twig', array(
      'title' => $this->get('translator')->trans('Users'),
      'main_menu_item' => 'users',
      'users' => $users
    ));
  }

  public function addUserAction(Request $request) {
    return $this->handleAddOrEditUser($request);
  }

  public function editUserAction(Request $request) {
    return $this->handleAddOrEditUser($request, $request->get('uid'));
  }

  public function deleteUserAction(Request $request) {
    if ($request->get('uid') != null) {
      $this->getIndexManager()->deleteObject($request->get('uid'));
      $this->addSessionMessage('status', $this->get('translator')->trans('User has been deleted'));
    } else {
      $this->addSessionMessage('error', $this->get('translator')->trans('No id provided'));
    }
    return $this->redirect($this->generateUrl('users'));
  }

  private function handleAddOrEditUser(Request $request, $uid = null) {
    if ($uid == null) { //Add
      $user = new User('', [], '', '', []);
    } else { //Edit
      /** @var User $user */
      $user = $this->getIndexManager()->findObject('user', $request->get('uid'));
    }
    /** @var Group[] $groups */
    $groups = $this->getIndexManager()->listObjects('group');
    $groupsChoices = [];
    foreach($groups as $group){
      $groupsChoices[$group->getName()] = $group->getId();
    }
    $roles = $this->container->getParameter('security.role_hierarchy.roles');
    $rolesChoices = [];
    foreach($roles as $k => $kk){
      if(!in_array($k, array_keys($rolesChoices))){
        $rolesChoices[$k] = $k;
      }
      foreach($kk as $kkk){
        if(!in_array($kkk, array_keys($rolesChoices))){
          $rolesChoices[$kkk] = $kkk;
        }
      }
    }
    ksort($rolesChoices);
    $form = $this->createFormBuilder($user)
      ->add('uid', TextType::class, array(
        'label' => $this->get('translator')->trans('Username'),
        'required' => true,
        'disabled' => $uid != null
      ))
      ->add('email', TextType::class, array(
        'label' => $this->get('translator')->trans('Email'),
        'required' => true,
      ))
      ->add('fullName', TextType::class, array(
        'label' => $this->get('translator')->trans('Full name'),
        'required' => true,
      ))
      ->add('newPassword', PasswordType::class, array(
        'label' => $this->get('translator')->trans('Password'),
        'mapped' => false,
        'required' => false,
      ))
      ->add('groups', ChoiceType::class, array(
        'label' => $this->get('translator')->trans('Groups'),
        'choices' => $groupsChoices,
        'required' => true,
        'expanded' => true,
        'multiple' => true
      ))
      ->add('roles', ChoiceType::class, array(
        'label' => $this->get('translator')->trans('Roles'),
        'choices' => $rolesChoices,
        'required' => true,
        'expanded' => true,
        'multiple' => true
      ))
      ->add('save', SubmitType::class, array('label' => $this->get('translator')->trans('Save')))
      ->getForm();
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
      /** @var User $user */
      $user = $form->getData();
      $user->setRoles(array_values($user->getRoles()));
      $plain = $form->get('newPassword')->getData();
      if(!empty($plain)){
        $encoder = $this->container->get('security.password_encoder');
        $encoded = $encoder->encodePassword($user, $plain);
        $user->setPassword($encoded);
      }
      if($uid == null) {
        $user->setCreatedBy($this->container->get('security.token_storage')->getToken()->getUser()->getUid());
      }
      $this->getIndexManager()->persistObject($user);
      if ($uid == null) {
        $this->addSessionMessage('status', $this->get('translator')->trans('New user has been added successfully'));
      } else {
        $this->addSessionMessage('status', $this->get('translator')->trans('User has been updated successfully'));
      }
      return $this->redirect($this->generateUrl('users'));
    }
    return $this->render('user.html.twig', array(
      'title' => $uid == null ? $this->get('translator')->trans('New user') : $this->get('translator')->trans('Edit user'),
      'main_menu_item' => 'users',
      'form' => $form->createView()
    ));
  }

  public function listGroupsAction(Request $request) {
    /** @var Group $groups */
    $groups = $this->getIndexManager()->listObjects('group');
    return $this->render('group.html.twig', array(
      'title' => $this->get('translator')->trans('Groups'),
      'main_menu_item' => 'groups',
      'groups' => $groups
    ));
  }

  public function addGroupAction(Request $request) {
    return $this->handleAddOrEditGroup($request);
  }

  public function editGroupAction(Request $request) {
    return $this->handleAddOrEditGroup($request, $request->get('id'));
  }

  public function deleteGroupAction(Request $request) {
    if ($request->get('id') != null) {
      $this->getIndexManager()->deleteObject($request->get('id'));
      $this->addSessionMessage('status', $this->get('translator')->trans('Group has been deleted'));
    } else {
      $this->addSessionMessage('error', $this->get('translator')->trans('No id provided'));
    }
    return $this->redirect($this->generateUrl('groups'));
  }


  private function handleAddOrEditGroup($request, $id = null) {
    if ($id == null) { //Add
      $group = new Group('', '', [], [], []);
    } else { //Edit
      /** @var Group $group */
      $group = $this->getIndexManager()->findObject('group', $request->get('id'));
    }
    $info = $this->getIndexManager()->getIndicesInfo();
    $indexChoices = [];
    foreach(array_keys($info) as $index){
      $indexChoices[$index] = $index;
    }
    /** @var Datasource[] $datasources */
    $datasources = $this->getIndexManager()->listObjects('datasource');
    $datasourceChoices = [];
    foreach($datasources as $ds){
      $datasourceChoices[$ds->getName()] = $ds->getId();
    }
    /** @var MatchingList[] $matchingLists */
    $matchingLists = $this->getIndexManager()->listObjects('matching_list');
    $matchingListsChoices = [];
    foreach($matchingLists as $item){
      $matchingListsChoices[$item->getName()] = $item->getId();
    }
    /** @var SynonymsDictionariesManager $sdManager */
    $sdManager = $this->container->get('adimeo_data_suite_synonyms_dictionaries_manager');
    try {
      $dictionaries = $sdManager->getDictionaries();
    }
    catch(DictionariesPathNotDefinedException $ex) {
      $dictionaries = [];
    }
    $dictionariesChoices = [];
    foreach($dictionaries as $item){
      $dictionariesChoices[$item['name']] = $item['name'];
    }
    $form = $this->createFormBuilder($group)
      ->add('id', TextType::class, array(
        'label' => $this->get('translator')->trans('ID'),
        'required' => true,
        'disabled' => $id != null
      ))
      ->add('name', TextType::class, array(
        'label' => $this->get('translator')->trans('Groupe name'),
        'required' => true,
      ))
      ->add('indexes', ChoiceType::class, array(
        'label' => $this->get('translator')->trans('Allowed indexes'),
        'choices' => $indexChoices,
        'required' => true,
        'expanded' => true,
        'multiple' => true
      ))
      ->add('datasources', ChoiceType::class, array(
        'label' => $this->get('translator')->trans('Allowed datasources'),
        'choices' => $datasourceChoices,
        'required' => true,
        'expanded' => true,
        'multiple' => true
      ))
      ->add('matchingLists', ChoiceType::class, array(
        'label' => $this->get('translator')->trans('Allowed matching lists'),
        'choices' => $matchingListsChoices,
        'required' => true,
        'expanded' => true,
        'multiple' => true
      ))
      ->add('dictionaries', ChoiceType::class, array(
        'label' => $this->get('translator')->trans('Allowed dictionaries'),
        'choices' => $dictionariesChoices,
        'required' => true,
        'expanded' => true,
        'multiple' => true
      ))
      ->add('save', SubmitType::class, array('label' => $this->get('translator')->trans('Save')))
      ->getForm();
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
      if($id == null) {
        $group->setCreatedBy($this->container->get('security.token_storage')->getToken()->getUser()->getUid());
      }
      $this->getIndexManager()->persistObject($form->getData());
      if ($id == null) {
        $this->addSessionMessage('status', $this->get('translator')->trans('New group has been added successfully'));
      } else {
        $this->addSessionMessage('status', $this->get('translator')->trans('Group has been updated successfully'));
      }
      return $this->redirect($this->generateUrl('groups'));
    }
    return $this->render('group.html.twig', array(
      'title' => $id == null ? $this->get('translator')->trans('New group') : $this->get('translator')->trans('Edit group'),
      'main_menu_item' => 'groups',
      'form' => $form->createView()
    ));
  }

}