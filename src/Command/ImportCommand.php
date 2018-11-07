<?php

namespace App\Command;

use AdimeoDataSuite\Model\PersistentObject;
use AdimeoDataSuite\Model\Processor;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCommand extends AdimeoDataSuiteCommand
{
  protected function configure()
  {
    $this
      ->setName('ads:import')
      ->setDescription('Import an entity')
      ->addArgument('type', InputArgument::REQUIRED, 'Entity type (datasource, processor, etc)')
      ->addArgument('override', InputArgument::OPTIONAL, 'Override existing index/mapping')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $type = $input->getArgument('type');
    $override = $input->getArgument('override') === 'true';
    $data = '';
    while($line = fgets(STDIN)){
      if($data != '') {
        $data .= "\n";
      }
      $data .= $line;
    }

    if($type == 'processor') {
      $proc = Processor::import($data, $this->getIndexManager(), $override);
      $output->writeln('Imported processor "' . $proc->getName() . '"');
    }
    else {
      $obj = PersistentObject::import($data, $this->getIndexManager(), $override);
      $output->writeln('Imported ' . $obj->getType() . ' "' . $obj->getName() . '"');
    }

  }
}