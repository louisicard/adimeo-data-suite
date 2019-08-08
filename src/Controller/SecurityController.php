<?php

namespace App\Controller;

use AdimeoDataSuite\Bundle\ADSSecurityBundle\Security\User;
use Elasticsearch\Common\Exceptions\NoNodesAvailableException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AdimeoDataSuiteController
{

  private $adsIndexNbShards;
  private $adsIndexNbReplicas;

  public function __construct($adsIndexNbShards, $adsIndexNbReplicas)
  {
    $this->adsIndexNbShards = $adsIndexNbShards;
    $this->adsIndexNbReplicas = $adsIndexNbReplicas;
  }

  public function loginAction(Request $request)
  {

    try {

      $this->getIndexManager()->initStore($this->adsIndexNbShards, $this->adsIndexNbReplicas);
      $users = $this->getIndexManager()->listObjects('user');
      if (empty($users)) {
        $user = new User('admin', array('ROLE_ADMIN'), 'admin@example.com', 'Administrator', array());
        $encoded = $this->container->get('security.password_encoder')->encodePassword($user, 'admin');
        $user->setPassword($encoded);
        $user->setCreatedBy('admin');
        $user->setCreated(new \DateTime());
        $this->getIndexManager()->persistObject($user);
      }

      /** @var AuthenticationUtils $authenticationUtils */
      $authenticationUtils = $this->get('security.authentication_utils');

      // get the login error if there is one
      $error = $authenticationUtils->getLastAuthenticationError();
      if ($error != null) {
        $errorText = get_class($error) == BadCredentialsException::class ? 'Bad credentials' : $error->getMessage();
      } else {
        $errorText = '';
      }

      // last username entered by the user
      $lastUsername = $authenticationUtils->getLastUsername();

      $noCluster = false;

    } catch (NoNodesAvailableException $ex) {
      $lastUsername = '';
      $error = true;
      $noCluster = true;
      $errorText = $ex->getMessage();
    }

    return $this->render('login.html.twig', array(
      'title' => $this->get('translator')->trans('Login'),
      'main_menu_item' => 'login',
      'no_menu' => true,
      'error' => $error,
      'errorText' => $errorText,
      'lastUsername' => $lastUsername,
      'noCluster' => $noCluster,
    ));
  }

  public function logoutAction(Request $request)
  {

  }
}
