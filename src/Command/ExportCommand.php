<?php

namespace App\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCommand extends AdimeoDataSuiteCommand
{
  protected function configure()
  {
    $this
      ->setName('ads:export')
      ->setDescription('Export an entity')
      ->addArgument('type', InputArgument::REQUIRED, 'Entity type (datasource, processor, etc)')
      ->addArgument('id', InputArgument::REQUIRED, 'Entity ID')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $type = $input->getArgument('type');
    $id = $input->getArgument('id');

    $obj = $this->getIndexManager()->findObject($type, $id);

    if($obj != null) {
      $output->write($obj->export($this->getIndexManager()));
    }
    else {
      throw new \Exception("Entity cannot be found!");
    }
  }
}