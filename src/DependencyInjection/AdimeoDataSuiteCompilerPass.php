<?php

namespace App\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AdimeoDataSuiteCompilerPass implements CompilerPassInterface
{
  public function process(ContainerBuilder $container)
  {
    $services = $container->findTaggedServiceIds("adimeodatasource.datasource");
    $container->setParameter("adimeodatasource.datasources", array_keys($services));
  }

}