<?php

namespace App\Command;

use AdimeoDataSuite\Model\Datasource;
use AdimeoDataSuite\Model\MatchingList;
use AdimeoDataSuite\Model\Processor;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateCommand extends AdimeoDataSuiteCommand
{
  protected function configure()
  {
    $this
      ->setName('ads:migrate')
      ->setDescription('Migrate a Ctsearch export file')
      ->addArgument('filePath', InputArgument::REQUIRED, 'Ctsearch export file path')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $filePath = $input->getArgument('filePath');
    $json = file_get_contents($filePath);
    if($json) {
      $json = json_decode($json, TRUE);
      $out = array();
      $out['index'][$json['index']['name']]['settings']['index'] = $json['index']['settings'];
      $out['mapping'][$json['mapping']['name']]['properties'] = $json['mapping']['definition'];
      $this->upgradeMapping($out['mapping'][$json['mapping']['name']]['properties']);
      if(isset($json['mapping']['dynamic_templates'])) {
        $out['mapping'][$json['mapping']['name']]['dynamic_templates'] = $json['mapping']['dynamic_templates'];
      }
      $out['datasource'] = json_encode(array('data' => serialize($this->migrateDatasource($json['datasource']))));
      if(isset($json['siblings'])) {
        foreach($json['siblings'] as $sibling) {
          $out['siblings'][] = json_encode(array('data' => serialize($this->migrateDatasource($sibling))));
        }
      }
      if(isset($json['matching_lists'])) {
        foreach($json['matching_lists'] as $matchingList) {
          $ml = new MatchingList($matchingList['name'], json_encode($matchingList['list']), $matchingList['id']);
          $ml->setCreated(new \DateTime());
          $ml->setCreatedBy(null);
          $ml->setUpdated(new \DateTime());
          $out['matching_lists'][] = serialize($ml);
        }
      }
      $proc = new Processor();
      $proc->setId($json['id']);
      $proc->setCreated(new \DateTime());
      $proc->setCreatedBy(null);
      $proc->setUpdated(new \DateTime());
      $proc->setTarget($json['processor_definition']['target']);
      $proc->setDatasourceId($json['datasource']['id']);
      $siblings = [];
      foreach($json['siblings'] as $sibling) {
        $siblings[] = $sibling['id'];
      }
      $proc->setTargetSiblings($siblings);

      $definition = $json['processor_definition'];
      foreach($definition['filters'] as $i => $filter) {
        $definition['filters'][$i]['class'] = str_replace("CtSearchBundle\\Processor", "AdimeoDataSuite\\ProcessorFilter", $filter['class']);
      }
      $proc->setDefinition(json_encode($definition, JSON_PRETTY_PRINT));

      $out['processor'] = serialize($proc);
      $output->write(json_encode($out, JSON_PRETTY_PRINT));
    }
    else {
      throw new \Exception('File path is incorrect');
    }
  }

  private function upgradeMapping(&$mapping) {
    foreach($mapping as $i => $field) {
      if(isset($field['type']) && $field['type'] == 'string') {
        if(isset($field['analyzer'])) {
          $mapping[$i]['type'] = 'text';
        }
        else {
          $mapping[$i]['type'] = 'keyword';
        }
      }
      if(isset($field['fields'])) {
        $this->upgradeMapping($mapping[$i]['fields']);
      }
    }
  }

  private function migrateDatasource($data) {
    $dsClass = str_replace("CtSearchBundle", "AdimeoDataSuite", $data['class']);
    if(class_exists($dsClass)) {
      /** @var Datasource $ds */
      $ds = new $dsClass();
      $ds->setCreated(new \DateTime());
      $ds->setCreatedBy(null);
      $ds->setUpdated(new \DateTime());
      $ds->setId($data['id']);
      $ds->setHasBatchExecution((bool)$data['has_batch_execution']);
      $settings = $data['settings'];
      $settings['name'] = $data['name'];
      $ds->setSettings($settings);

      return $ds;
    }
    else {
      throw new Exception('Class "' . $data['class'] . '" has no equivalent in ADS!');
    }
  }
}