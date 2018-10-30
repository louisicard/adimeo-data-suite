<?php

namespace App\Controller;


use AdimeoDataSuite\Model\Parameter;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;

class ParameterController extends AdimeoDataSuiteController
{

  public function listParametersAction(Request $request) {
    $parameters = $this->getIndexManager()->listObjects('parameter', $this->buildSecurityContext());
    return $this->render('parameters.html.twig', array(
      'title' => $this->get('translator')->trans('Parameters'),
      'main_menu_item' => 'parameters',
      'parameters' => $parameters
    ));
  }

  public function addParameterAction(Request $request) {
    return $this->handleAddOrEditParameter($request);
  }

  public function editParameterAction(Request $request) {
    return $this->handleAddOrEditParameter($request, $request->get('id'));
  }

  public function deleteParameterAction(Request $request) {
    if ($request->get('id') != null) {
      $this->getIndexManager()->deleteObject($request->get('id'));
      $this->addSessionMessage('status', $this->get('translator')->trans('Parameter has been deleted'));
    } else {
      $this->addSessionMessage('error', $this->get('translator')->trans('No id provided'));
    }
    return $this->redirect($this->generateUrl('parameters'));
  }


  private function handleAddOrEditParameter($request, $id = null)
  {
    if ($id == null) { //Add
      $parameter = new Parameter('', '');
    } else { //Edit
      /** @var Parameter $parameter */
      $parameter = $this->getIndexManager()->findObject('parameter', $request->get('id'));
    }
    $form = $this->createFormBuilder($parameter)
      ->add('name', TextType::class, array(
        'label' => $this->get('translator')->trans('Name'),
        'required' => true,
        'disabled' => $id != null
      ))
      ->add('value', TextType::class, array(
        'label' => $this->get('translator')->trans('Value'),
        'required' => true,
      ))
      ->add('save', SubmitType::class, array('label' => $this->get('translator')->trans('Save')))
      ->getForm();
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
      if($id == null){
        $parameter->setCreatedBy($this->container->get('security.token_storage')->getToken()->getUser()->getUid());
      }
      $this->getIndexManager()->persistObject($parameter);
      if ($id == null) {
        $this->addSessionMessage('status', $this->get('translator')->trans('New parameter has been added successfully'));
      } else {
        $this->addSessionMessage('status', $this->get('translator')->trans('Parameter has been updated successfully'));
      }
      return $this->redirect($this->generateUrl('parameters'));
    }

    return $this->render('parameters.html.twig', array(
      'title' => $id == null ? $this->get('translator')->trans('New parameter') : $this->get('translator')->trans('Edit parameter'),
      'main_menu_item' => 'parameters',
      'form' => $form->createView()
    ));
  }
}