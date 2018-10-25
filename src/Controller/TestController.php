<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TestController extends AdimeoDataSuiteController
{

    public function testAction(Request $request)
    {
      var_dump($this->getIndexManager()->getServerInfo());
      return new Response('Je me teste', 200);
    }
}
