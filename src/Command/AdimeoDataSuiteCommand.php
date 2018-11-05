<?php
/**
 * Created by PhpStorm.
 * User: louis
 * Date: 30/10/2018
 * Time: 19:35
 */

namespace App\Command;


use AdimeoDataSuite\Index\IndexManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

abstract class AdimeoDataSuiteCommand extends ContainerAwareCommand
{
  /**
   * @return IndexManager
   */
  protected function getIndexManager() {
    return $this->getContainer()->get('adimeo_data_suite_es_server');
  }
}