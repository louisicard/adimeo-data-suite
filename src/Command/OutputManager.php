<?php
/**
 * Created by PhpStorm.
 * User: louis
 * Date: 30/10/2018
 * Time: 19:43
 */

namespace App\Command;


use Symfony\Component\Console\Output\OutputInterface;

class OutputManager implements \AdimeoDataSuite\Model\OutputManager
{
  private $output;

  public function __construct(OutputInterface $output)
  {
    $this->output = $output;
  }

  function writeLn($text)
  {
    $this->output->writeln($text);
  }

}