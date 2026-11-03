<?php

namespace App\Libraries;

use CodeIgniter\HTTP\RequestInterface;

class SomeClass
{
    protected $request;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }
}

$someClass = new SomeClass(service('request'));
