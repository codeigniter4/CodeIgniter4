<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\CodeIgniter;

class Ping extends BaseController
{
    use ResponseTrait;

    public function getIndex()
    {
        return $this->respond([
            'status'  => 'ok',
            'time'    => date('c'),
            'version' => CodeIgniter::CI_VERSION,
        ]);
    }
}
