<?php

namespace App\Command;

use AdimeoDataSuite\Model\SavedQuery;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteByQueryCommand extends AdimeoDataSuiteCommand {
  
  protected function configure(){
    $this
        ->setName('ads:delete-by-query')
        ->setDescription('Delete records by query')
        ->addArgument('id', InputArgument::REQUIRED, 'Saved query id')
        ->addOption('no-proxy', null, InputOption::VALUE_NONE, 'Bypass proxy to connect to ES server')
    ;
  }
  protected function execute(InputInterface $input, OutputInterface $output)
    {
      if($input->getOption('no-proxy')){
        $proxy = getenv("http_proxy");
        putenv("http_proxy=");
      }
      $id = $input->getArgument('id');
      /** @var SavedQuery $query */
      $query = $this->getIndexManager()->findObject('saved_query', $id);
      if($query != null) {
        $output->writeln('Query def => ' . json_encode(json_decode($query->getDefinition())) . '');
        $output->writeln('Query target => ' . $query->getTarget() . '');
        $index = strpos($query->getTarget(), '.') !== 0 ? explode('.', $query->getTarget())[0] : '.' . explode('.', $query->getTarget())[1];
        $mapping = strpos($query->getTarget(), '.') !== 0 ? explode('.', $query->getTarget())[1] : explode('.', $query->getTarget())[2];
        $output->writeln('Index name => ' . $index . '');
        $output->writeln('Mapping name => ' . $mapping . '');
        $r = $this->getIndexManager()->search($index, json_decode($query->getDefinition(), true), 0, 0, $mapping);
        if(isset($r['hits']['total'])){
          $output->writeln('Found ' . $r['hits']['total'] . ' matching record(s)');
        }
        $this->getIndexManager()->deleteByQuery($index, $mapping, json_decode($query->getDefinition(), true));
        $output->writeln('Query has been executed for deletion');
      }
      else{
        $output->writeln('ERROR : Query could not be found');
      }
    }
}
