<?php

namespace App\Command;


use Symfony\Component\Console\Output\OutputInterface;
use AdimeoDataSuite\Model\OutputManager;

class CommandOutputManager implements OutputManager
{
  private $output;

  public function __construct(OutputInterface $output)
  {
    $this->output = $output;
  }

  function writeLn($text)
  {
    if(is_object($text)) {
      ob_start();
      print_r($text);
      $r = ob_get_clean();
      $this->writeLn($r);
    }
    else {
      $this->output->writeln($text);
    }
  }

  function dumpArray($array, $depth = 0)
  {
    $indent = '';
    for($i = 0; $i < $depth; $i++)
      $indent .= ' ';
    if(is_array($array)) {
      $this->output->writeln('Array(');
      foreach($array as $k => $v) {
        if(!is_array($v))
          $this->writeln($indent . '  [' . $k . '] => ' . $v);
        else {
          $this->output->write($indent . '  [' . $k . '] => ');
          $this->dumpArray($v, $depth + 2);
        }
      }
      $this->output->writeln($indent . ')');
    }
  }


}