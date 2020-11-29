<?php

namespace Http\Psr7Test\Tests;

use CodeIgniter\HTTP\Response;
use Config\App;
use Http\Psr7Test\ResponseIntegrationTest;

class ResponseTest extends ResponseIntegrationTest
{
    public function createSubject()
    {
        return new Response(new App());
    }
}
