<?php

namespace App\Command;

use AdimeoDataSuite\Datasource\OAIHarvester;
use AdimeoDataSuite\Model\Datasource;
use AdimeoDataSuite\PDO\PDOPool;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OAICommand extends AdimeoDataSuiteCommand
{
  protected function configure()
  {
    $this
      ->setName('ads:oai')
      ->setDescription('Launch OAI Harvester')
      ->addArgument('id', InputArgument::REQUIRED, 'Datasource id')
      ->addArgument('token', InputArgument::OPTIONAL, 'Resumption token')
      ->addArgument('run', InputArgument::OPTIONAL, 'Run')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $id = $input->getArgument('id');
    $token = $input->getArgument('token');
    if($token == 'NULL')
      $token = null;
    $run = $input->getArgument('run');
    if($run != null){
      /** @var Datasource $datasource */
      $datasource = $this->getIndexManager()->findObject('datasource', $id);
      if(get_class($datasource) == OAIHarvester::class){
        $datasource->initForExecution($this->getIndexManager(), new CommandOutputManager($output), $this->getContainer()->get('adimeo_data_suite_pdo_pool'));
        $output->writeln('Executing OAI Harvester "' . $datasource->getName() . '"');
        /** @var OAIHarvester $datasource */
        $datasource->runCli($token);
      }
    }
    else{
      $code = 0;
      $out = '';
      exec(PHP_BINARY . ' bin/console ads:oai ' . $id . ' NULL run', $out, $code);
      while($code == 9){
        $token  = $out[count($out) - 1];
        $retry = 10;
        while($retry > 0) {
          print 'Resuming with token ' . $token . PHP_EOL;
          exec(PHP_BINARY . ' bin/console ads:oai ' . $id . ' "' . $token . '" run', $out, $code);
          if($code == 0 || $code == 9) {
            $retry = 0;
          }
          else {
            print 'OAI fetch failed (' . $code . '). Sleeping 60 seconds before retrying.' . PHP_EOL;
            $retry--;
            sleep(60);
          }
        }
      }
    }
  }
}