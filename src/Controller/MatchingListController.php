<?php

namespace App\Controller;


use AdimeoDataSuite\Model\MatchingList;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MatchingListController extends AdimeoDataSuiteController
{
  public function listMatchingListsAction(Request $request) {
    return $this->render('matching-lists.html.twig', array(
      'title' => $this->get('translator')->trans('Matching lists'),
      'main_menu_item' => 'matching-lists',
      'matching_lists' => $this->getIndexManager()->listObjects('matching_list', $this->buildSecurityContext()),
    ));
  }

  public function addMatchingListAction(Request $request) {
    return $this->handleAddOrEditMatchingList($request);
  }

  public function editMatchingListAction(Request $request) {
    return $this->handleAddOrEditMatchingList($request, $request->get('id'));
  }

  private function handleAddOrEditMatchingList(Request $request, $id = null) {
    if ($id == null) { //Add
      $matchingList = new MatchingList('');
    } else { //Edit
      /** @var MatchingList $matchingList */
      $matchingList = $this->getIndexManager()->findObject('matching_list', $request->get('id'));
      $list = $matchingList->getList();
      //ksort($list, SORT_NATURAL | SORT_FLAG_CASE);
      if (empty($list))
        $matchingList->setList('{}');
    }
    $form = $this->createFormBuilder($matchingList)
      ->add('id', HiddenType::class)
      ->add('name', TextType::class, array(
        'label' => $this->get('translator')->trans('Matching list name'),
        'required' => true,
      ))
      ->add('list', TextareaType::class, array(
        'label' => $this->get('translator')->trans('Matching list definition'),
        'required' => true
      ))
      ->add('save', SubmitType::class, array('label' => $this->get('translator')->trans('Save')))
      ->getForm();
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
      if (!is_array(json_decode($matchingList->getList())) && json_decode($matchingList->getList()) == null) {
        $this->addSessionMessage('error', $this->get('translator')->trans('JSON parsing failed.'));
      } else {
        $matchingList = $form->getData();
        $def = json_decode($matchingList->getList());
        if (empty($def))
          $matchingList->setList('{}');
        if($id == null) {
          $matchingList->setCreatedBy($this->container->get('security.token_storage')->getToken()->getUser()->getUid());
        }
        $this->getIndexManager()->persistObject($matchingList);
        if ($id == null) {
          $this->addSessionMessage('status', $this->get('translator')->trans('New matching list has been added successfully'));
        } else {
          $this->addSessionMessage('status', $this->get('translator')->trans('Matching list has been updated successfully'));
        }
        if ($id == null)
          return $this->redirect($this->generateUrl('matching-lists'));
        else {
          return $this->redirect($this->generateUrl('matching-list-edit', array('id' => $id)));
        }
      }
    }
    $vars = array(
      'title' => $id == null ? $this->get('translator')->trans('New matching list') : $this->get('translator')->trans('Edit matching list'),
      'main_menu_item' => 'matching-lists',
      'form' => $form->createView()
    );
    if ($id != null) {

      $infos = $this->getIndexManager()->getIndicesInfo($this->buildSecurityContext());
      $select = '<select id="matching-list-field-selector"><option value="">Select a field</option>';
      foreach ($infos as $index => $info) {
        if(isset($info['mappings'])) {
          $select .= '<optgroup label="' . htmlentities($index) . '">';
          foreach ($info['mappings'] as $mappingInfo) {
            $mapping = $this->getIndexManager()->getMapping($index, $mappingInfo['name']);
            foreach ($mapping['properties'] as $field => $info_field) {
              $select .= '<option value="' . $index . '.' . $mappingInfo['name'] . '.' . $field . '">' . $index . '.' . $mappingInfo['name'] . '.' . $field . '</option>';
            }
          }
          $select .= '</optgroup>';
        }
      }
      $select .= '</select>';
      $select_size = '<select id="matching-list-size-selector"><option value="">Select max number of values to import</option>';
      $select_size .= '<option value="20">20</option>';
      $select_size .= '<option value="50">50</option>';
      $select_size .= '<option value="100">100</option>';
      $select_size .= '<option value="200">200</option>';
      $select_size .= '<option value="300">300</option>';
      $select_size .= '</select>';
      $vars['init_from_index_action'] = $select . $select_size . '<a href="' . $this->generateUrl('matching-init-from-index', array('id' => $id)) . '" class="fa fa-play-circle">' . $this->get('translator')->trans('Initialize from index') . '</a>';
      $vars['import_file_link'] = $this->generateUrl('matching-import-file', array('id' => $id));
      $vars['export_link'] = $this->generateUrl('matching-export', array('id' => $id));
    }
    return $this->render('matching-lists.html.twig', $vars);
  }

  public function deleteMatchingListAction(Request $request) {
    if ($request->get('id') != null) {
      $this->getIndexManager()->deleteObject($request->get('id'));
      $this->addSessionMessage('status', $this->get('translator')->trans('Matching list has been deleted'));
    } else {
      $this->addSessionMessage('error', $this->get('translator')->trans('No id provided'));
    }
    return $this->redirect($this->generateUrl('matching-lists'));
  }

  public function importMatchingListFileAction(Request $request) {
    if ($request->get('id') != null) {
      /** @var MatchingList $matchingList */
      $matchingList = $this->getIndexManager()->findObject('matching_list', $request->get('id'));
      $form = $this->createFormBuilder()
        ->add('matching_list_id', HiddenType::class, array(
          'data' => $matchingList->getId()
        ))
        ->add('import_file', FileType::class, array(
          'label' => 'File to import (CSV comma separated with no headers)',
          'required' => true
        ))
        ->add('ok', SubmitType::class, array(
          'label' => 'Import',
        ))
        ->getForm();
      $form->handleRequest($request);
      if ($form->isSubmitted() && $form->isValid()) {
        $data = $form->getData();
        /** @var UploadedFile $file */
        $file = $data['import_file'];
        $extension = pathinfo($file->getClientOriginalName())['extension'];
        if (strtolower($extension) != 'csv') {
          $this->addSessionMessage('error', $this->get('translator')->trans('Only CSV files can be imported'));
        } else {
          $fp = fopen($file->getRealPath(), 'r');
          $list = array();
          while(($line = fgetcsv($fp))) {
            if (count($line) >= 2) {
              $list[$line[0]] = $line[1];
            }
          }
          fclose($fp);
          unlink($file->getRealPath());
          $matchingList->setList(json_encode($list));
          $this->getIndexManager()->persistObject($matchingList);
          $this->addSessionMessage('status', $this->get('translator')->trans(count($list) . ' values imported'));
          return $this->redirect($this->generateUrl('matching-lists'));
        }
      }
      return $this->render('matching-lists.html.twig', array(
        'title' => $this->get('translator')->trans('Import file'),
        'main_menu_item' => 'matching-lists',
        'form' => $form->createView(),
        'matchingList' => $matchingList
      ));
    } else {
      $this->addSessionMessage('error', $this->get('translator')->trans('No id provided'));
      return $this->redirect($this->generateUrl('matching-lists'));
    }
  }

  public function exportMatchingListFileAction(Request $request) {
    if ($request->get('id') != null) {
      /** @var MatchingList $matchingList */
      $matchingList = $this->getIndexManager()->findObject('matching_list', $request->get('id'));
      $list = json_decode($matchingList->getList(), true);
      $data = '';
      foreach ($list as $k => $v) {
        $data .= '"' . $k . '","' . $v . "\"\r\n";
      }
      $response = new Response($data, 200, array(
        'Content-type' => 'text/csv; encoding=utf-8',
        'Content-disposition' => 'attachment; filename=export.csv',
      ));
      return $response;
    } else {
      $this->addSessionMessage('error', $this->get('translator')->trans('No id provided'));
      return $this->redirect($this->generateUrl('matching-lists'));
    }
  }

  public function initMatchingListFromIndexAction(Request $request) {
    if ($request->get('id') != null && $request->get('field') != null && $request->get('size') != null) {
      /** @var MatchingList $matchingList */
      $matchingList = $this->getIndexManager()->findObject('matching_list', $request->get('id'));
      $dottedIndex = strpos($request->get('field'), '.') === 0;
      $field_data = explode('.', $request->get('field'));
      $indexName = !$dottedIndex ? $field_data[0] : '.' . $field_data[1];
      $mappingName = !$dottedIndex ? $field_data[1] : $field_data[2];
      $field = !$dottedIndex ? $field_data[2] : $field_data[3];
      $query = array(
        'query' => array(
          'type' => array(
            'value' => $mappingName
          )
        ),
        'aggs' => array(
          'values' => array(
            'terms' => array(
              'field' => $field,
              'order' => array(
                '_count' => 'desc'
              ),
              'size' => $request->get('size')
            )
          )
        )
      );
      $result = $this->getIndexManager()->search($indexName, $query);
      $list = array();
      if (isset($result['aggregations']['values']['buckets'])) {
        foreach ($result['aggregations']['values']['buckets'] as $bucket) {
          $list[$bucket['key']] = '';
        }
      }
      $matchingList->setList(json_encode($list));
      $this->getIndexManager()->persistObject($matchingList);
      $this->addSessionMessage('status', $this->get('translator')->trans(count($list) . ' values imported'));
      return $this->redirect($this->generateUrl('matching-lists'));
    } else {
      $this->addSessionMessage('error', $this->get('translator')->trans('No id or field or size provided'));
      return $this->redirect($this->generateUrl('matching-lists'));
    }
  }
}