<?php

namespace App\Controller;

use Elasticsearch\Common\Exceptions\BadRequest400Exception;
use Elasticsearch\Common\Exceptions\ServerErrorResponseException;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;


class BackupsController extends AdimeoDataSuiteController
{
    /**
     * @Route("/backups", name="backups")
     * @param Request $request
     * @return Response
     */
    public function listBackupsAction(Request $request)
    {
        $repos = $this->getBackupsManager()->getBackupsRepositories();
        $snapshots = array();
        foreach (array_keys($repos) as $repo) {
            $s = $this->getBackupsManager()->getSnapshots($repo);
            if (isset($s['snapshots']) && count($s['snapshots']) > 0) {
                foreach ($s['snapshots'] as $i => $snap) {
                    if (isset($snap['end_time_in_millis'])) {
                        $s['snapshots'][$i]['end_time_clean'] = date('Y-m-d H:i:s',
                            round($snap['end_time_in_millis'] / 1000));
                    }
                }
                $snapshots[$repo] = $s['snapshots'];
            }
        }
        $params = array(
            'title' => $this->get('translator')->trans('Backups'),
            'main_menu_item' => 'backups',
            'repos' => $repos,
            'snapshots' => $snapshots
        );
        return $this->render('backups.html.twig', $params);
    }

    /**
     * Create or edit a repository
     *
     * @Route("/backups/create-repo", name="backups_create_repo")
     * @Route("/backups/edit-repo/{repositoryName}", name="backups_edit_repo")
     * @param Request $request
     * @param string $repositoryName
     * @return Response
     */
    public function createOrEditRepoAction(Request $request, $repositoryName = null)
    {
        if ($repositoryName != null) {
            $repo = $this->getBackupsManager()->getRepository($repositoryName);
            $repo_name = array_keys($repo)[0];
            $data = array(
                'name' => $repo_name,
                'type' => $repo[$repo_name]['type'],
                'compress' => $repo[$repo_name]['settings']['compress'] == 'true',
                'location' => $repo[$repo_name]['settings']['location'],
            );
        } else {
            $data = null;
        }

        $form = $this->createFormBuilder($data)
            ->add('name', TextType::class, array(
                'label' => $this->get('translator')->trans('Name'),
                'required' => true,
                'disabled' => $repositoryName != null
            ))
            ->add('type', ChoiceType::class, array(
                'label' => $this->get('translator')->trans('Type'),
                'required' => true,
                'choices' => array(
                    $this->get('translator')->trans('Select') => '',
                    $this->get('translator')->trans('File system') => 'fs'
                )
            ))
            ->add('location', TextType::class, array(
                'label' => $this->get('translator')->trans('Location (must be declared in Elastic conf [path.repo])'),
                'required' => true,
            ))
            ->add('compress', CheckboxType::class, array(
                'label' => $this->get('translator')->trans('Compressed'),
                'required' => false,
            ))
            ->add('submit', SubmitType::class, array(
                'label' => $this->get('translator')->trans('Submit')
            ))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->getBackupsManager()->createRepository($form->getData());
                $this->addSessionMessage('status',
                    $this->get('translator')->trans('Repository has been ' . ($repositoryName == null ? 'created' : 'updated')));
                return $this->redirect($this->generateUrl('backups'));
            } catch (ServerErrorResponseException $ex) {
                $this->addSessionMessage('error',
                    $this->get('translator')->trans('Repository could not be created please check your settings'));
            }
        }

        $params = array(
            'title' => $this->get('translator')->trans(($repositoryName == null ? 'Create' : 'Edit') . ' a repository'),
            'main_menu_item' => 'backups',
            'form' => $form->createView()
        );
        return $this->render('backups-form.html.twig', $params);
    }


    /**
     * Delete an existing repository
     *
     * @Route("/backups/delete-repo/{repositoryName}", name="backups_delete_repo")
     * @param Request $request
     * @param string $repositoryName
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteRepoAction(Request $request, $repositoryName)
    {
        try {
            $this->getBackupsManager()->deleteRepository($repositoryName);
            $this->addSessionMessage('status', $this->get('translator')->trans('Repository has deleted'));

        } catch (ServerErrorResponseException $ex) {
            $this->addSessionMessage('error',
                $this->get('translator')->trans('Repository could not be deleted please check your settings'));
        }
        return $this->redirect($this->generateUrl('backups'));
    }

    /**
     * Create a snapshot
     *
     * @Route("/backups/snapshot/create", name="backups_create_snapshot")
     * @param Request $request
     * @return Response
     */
    public function createSnapshotAction(Request $request)
    {
        $repos = $this->getBackupsManager()->getBackupsRepositories();
        $repoChoices = array(
            $this->get('translator')->trans('Select') => ''
        );
        foreach (array_keys($repos) as $repo) {
            $repoChoices[$repo] = $repo;
        }
        $info = $this->getIndexManager()->getElasticInfo($this->buildSecurityContext());
        $indexChoices = array();
        foreach ($info as $k => $data) {
            $indexChoices[$k] = $k;
        }
        ksort($indexChoices);
        $form = $this->createFormBuilder(null)
            ->add('name', TextType::class, array(
                'label' => $this->get('translator')->trans('Snapshot name'),
                'required' => true
            ))
            ->add('repo', ChoiceType::class, array(
                'label' => $this->get('translator')->trans('Repository'),
                'choices' => $repoChoices,
                'required' => true
            ))
            ->add('indexes', ChoiceType::class, array(
                'label' => $this->get('translator')->trans('Indexes to backup'),
                'choices' => $indexChoices,
                'required' => true,
                'expanded' => true,
                'multiple' => true
            ))
            ->add('ignoreUnavailable', CheckboxType::class, array(
                'label' => $this->get('translator')->trans('Ignore unavailable'),
                'required' => false,
            ))
            ->add('includeGlobalState', CheckboxType::class, array(
                'label' => $this->get('translator')->trans('Include global state'),
                'required' => false,
            ))
            ->add('submit', SubmitType::class, array(
                'label' => $this->get('translator')->trans('Submit')
            ))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $this->getBackupsManager()->createSnapshot($data['repo'], $data['name'], $data['indexes'], $data['ignoreUnavailable'], $data['includeGlobalState']);
                $this->addSessionMessage('status', $this->get('translator')->trans('Snapshot has been created'));
                return $this->redirect($this->generateUrl('backups'));
            } catch (ServerErrorResponseException $ex) {
                $this->addSessionMessage('error', $this->get('translator')->trans('Snapshot could not be created please check your settings'));
            } catch (BadRequest400Exception $ex2) {
                $this->addSessionMessage('error', $this->get('translator')->trans('Snapshot could not be created : ' . $ex2->getMessage()));
            }
        }

        $params = array(
            'title' => $this->get('translator')->trans('Create a snapshot'),
            'main_menu_item' => 'backups',
            'form' => $form->createView()
        );
        return $this->render('backups-form.html.twig', $params);
    }

    /**
     * @Route("/backups/delete-snapshot/{repositoryName}/{snapshotName}", name="backups_delete_snapshot")
     * @param Request $request
     * @param string $repositoryName
     * @param string $snapshotName
     * @return RedirectResponse
     */
    public function deleteSnapshot(Request $request, $repositoryName, $snapshotName)
    {
        try {
            $this->getBackupsManager()->deleteSnapshot($repositoryName, $snapshotName);
            $this->addSessionMessage('status', $this->get('translator')->trans('Snapshot has deleted'));

        } catch (ServerErrorResponseException $ex) {
            $this->addSessionMessage('error', $this->get('translator')->trans('Snapshot could not be deleted please check your settings'));
        }
        return $this->redirect($this->generateUrl('backups'));
    }

    /**
     * @Route("/backups/snapshot/restore/{repositoryName}/{snapshotName}", name="backups_restore_snapshot")
     * @param Request $request
     * @param string $repositoryName
     * @param string $snapshotName
     * @return Response
     */
    public function restoreSnapshotAction(Request $request, $repositoryName, $snapshotName)
    {

        $snapshot = $this->getBackupsManager()->getSnapshot($repositoryName, $snapshotName);

        $indexesChoices = array();
        foreach ($snapshot['indices'] as $index) {
            $indexesChoices[$index] = $index;
        }
        ksort($indexesChoices);

        $form = $this->createFormBuilder(null)
            ->add('indexes', ChoiceType::class, array(
                'label' => $this->get('translator')->trans('Indexes to restore (if none selected, all will be restored)'),
                'required' => true,
                'expanded' => true,
                'multiple' => true,
                'choices' => $indexesChoices
            ))
            ->add('renamePattern', TextType::class, array(
                'label' => $this->get('translator')->trans('Rename pattern (cf https://www.elastic.co/guide/en/elasticsearch/reference/current/modules-snapshots.html)'),
                'required' => false,
                'data' => '(.+)'
            ))
            ->add('renameReplacement', TextType::class, array(
                'label' => $this->get('translator')->trans('Rename replacement (cf https://www.elastic.co/guide/en/elasticsearch/reference/current/modules-snapshots.html)'),
                'required' => false,
                'data' => 'restored_$1'
            ))
            ->add('ignoreUnavailable', CheckboxType::class, array(
                'label' => $this->get('translator')->trans('Ignore unavailable'),
                'required' => false,
            ))
            ->add('includeGlobalState', CheckboxType::class, array(
                'label' => $this->get('translator')->trans('Include global state'),
                'required' => false,
            ))
            ->add('submit', SubmitType::class, array(
                'label' => $this->get('translator')->trans('Submit')
            ))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            try {
                $this->getBackupsManager()->restoreSnapshot($repositoryName, $snapshotName, $data);
                $this->addSessionMessage('status', $this->get('translator')->trans('Snapshot has been restored'));
            } catch (ServerErrorResponseException $ex) {
                $this->addSessionMessage('error', $this->get('translator')->trans('Snapshot could not be restored : ' . $ex->getMessage()));
            } catch (BadRequest400Exception $ex2) {
                $this->addSessionMessage('error', $this->get('translator')->trans('Snapshot could not be restored : ' . $ex2->getMessage()));
            }
        }

        $params = array(
            'title' => $this->get('translator')->trans('Restore a snapshot'),
            'main_menu_item' => 'backups',
            'form' => $form->createView()
        );
        return $this->render('backups-form.html.twig', $params);
    }

}