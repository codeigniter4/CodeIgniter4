<?php

namespace App\Libraries;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\Response;

class MyCustomClass
{
    use ResponseTrait;

    protected Response $response;

    public function __construct()
    {
        // Manually create a Response object
        $this->response = service('response');
    }

    public function createUser()
    {
        return $this->respondCreated(['message' => 'User created.']);
    }
}
