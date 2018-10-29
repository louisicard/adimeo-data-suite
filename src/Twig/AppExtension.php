<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{

  public function getFilters() {
    return array(
      new TwigFilter('getClass', array($this, 'getClass')),
      new TwigFilter('jsonDecode', array($this, 'jsonDecode'))
    );
  }

  public function getClass($object) {
    return get_class($object);
  }

  public function jsonDecode($object) {
    return json_decode($object, true);
  }

}