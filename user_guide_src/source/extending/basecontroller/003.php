<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function initController(/* ... */)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        $this->session = \Config\Services::session();
    }
}
