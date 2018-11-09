<?php

namespace App\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AdimeoDataSuiteCompilerPass implements CompilerPassInterface
{
  public function process(ContainerBuilder $container)
  {
    $services = $container->findTaggedServiceIds("adimeodatasuite.datasource");
    $container->setParameter("adimeodatasuite.datasources", array_keys($services));
    $filtersServices = $container->findTaggedServiceIds("adimeodatasuite.filter");
    $container->setParameter("adimeodatasuite.filters", array_keys($filtersServices));
    $statServices = $container->findTaggedServiceIds("adimeodatasuite.stat");
    $container->setParameter("adimeodatasuite.stats", array_keys($statServices));
  }

}