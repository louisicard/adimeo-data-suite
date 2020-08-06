<?php

namespace App\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportIndexCommand extends AdimeoDataSuiteCommand
{

  /**
   * @var OutputInterface
   */
  private $output;
  /**
   * @var InputInterface
   */
  private $input;

  protected function configure()
  {
    $this
      ->setName('ads:restore')
      ->addOption('bulkSize', 's', InputOption::VALUE_OPTIONAL, "Bulk index size", 1000)
      ->addOption('booleanFieldsToFix', 'b', InputOption::VALUE_OPTIONAL, "Boolean fields to fix (comma separated)", 1000)
      ->setDescription('Restoring index tool')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->input = $input;
    $this->output = $output;
    while($line = fgets(STDIN)){
      $this->index($line, $input->getOption('bulkSize'));
    }
    $this->getIndexManager()->bulkIndex($this->buffer);
    $this->getIndexManager()->flush();
    $this->output->writeln($this->total . ' documents indexed');
  }

  private $count = 0;
  private $total = 0;
  private $buffer = [];

  private function index($item, $bulkSize) {
    $data = json_decode($item, TRUE);
    $data['_source']['_id'] = $data['_id'];
    $this->count++;
    $this->total++;

    $boolFields = $this->input->getOption('booleanFieldsToFix');
    if($boolFields != null && !empty($boolFields)) {
      $fields = array_filter(explode(',', $boolFields), 'trim');
      foreach($data['_source'] as $k => $v) {
        if(in_array($k, $fields)) {
          $data['_source'][$k] = $v === '0' || $v === 0 || $v === false ? false : true;
        }
      }
    }

    $this->buffer[] = array(
      'indexName' => $data['_index'],
      'mappingName' => $data['_type'],
      'body' => $data['_source'],
    );
    if($this->count >= $bulkSize) {
      $this->getIndexManager()->bulkIndex($this->buffer);
      $this->buffer = [];
      $this->count = 0;
      $this->output->writeln($this->total . ' documents indexed so far');
    }
  }

}
