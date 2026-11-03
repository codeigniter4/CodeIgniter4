<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class Ping extends BaseController
{
    use ResponseTrait;

    public function getIndex(): ResponseInterface
    {
        return $this->respond(['status' => 'ok'], 200);
    }
}
