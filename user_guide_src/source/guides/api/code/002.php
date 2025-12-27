<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Ping extends BaseController
{
    use ResponseTrait;

    protected $format = 'json';

    public function getIndex()
    {
        return $this->respond(['status' => 'ok'], 200);
    }
}
