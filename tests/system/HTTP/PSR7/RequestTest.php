<?php

namespace Http\Psr7Test\Tests;

use CodeIgniter\HTTP\Request;
use Config\App;
use Http\Psr7Test\RequestIntegrationTest;

class RequestTest extends RequestIntegrationTest
{
    public function createSubject()
    {
        return new Request(new App());
    }
}
