<?php

namespace App\Controllers;

use CodeIgniter\Controller;

abstract class BaseController extends Controller
{
    // ...

    public function initController(/* ... */)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        $this->session = \Config\Services::session();
    }
}
