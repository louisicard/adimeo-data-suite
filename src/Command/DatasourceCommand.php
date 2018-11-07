<?php

namespace App\Command;

use AdimeoDataSuite\Model\Datasource;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DatasourceCommand extends AdimeoDataSuiteCommand
{
  protected function configure()
  {
    $this
      ->setName('ads:exec')
      ->setDescription('Execute processor chains for the given datasource')
      ->addArgument('datasourceId', InputArgument::REQUIRED, 'Datasource ID')
      ->addArgument('arg1', InputArgument::OPTIONAL, 'Datasource execution arg1')
      ->addArgument('arg2', InputArgument::OPTIONAL, 'Datasource execution arg2')
      ->addArgument('arg3', InputArgument::OPTIONAL, 'Datasource execution arg3')
      ->addArgument('arg4', InputArgument::OPTIONAL, 'Datasource execution arg4')
      ->addArgument('arg5', InputArgument::OPTIONAL, 'Datasource execution arg5')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $datasourceId = $input->getArgument('datasourceId');
    /** @var Datasource $datasource */
    $datasource = $this->getIndexManager()->findObject('datasource', $datasourceId);
    if($datasource == null) {
      throw new \Exception('No datasource found for ID "' . $datasourceId . '"');
    }

    $datasource->initForExecution($this->getIndexManager(), new CommandOutputManager($output), $this->getContainer()->get('adimeo_data_suite_pdo_pool'));

    $args = [];
    for($i = 1; $i <= 5; $i++) {
      if($input->getArgument('arg' . $i) != null) {
        $args[$i - 1] = $datasource->injectParameters($input->getArgument('arg' . $i));
      }
    }

    $datasource->startExecution($args);
  }
}