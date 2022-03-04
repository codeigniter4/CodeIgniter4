<?php

namespace App\Controllers;

class MyController extends BaseController
{
    public function index()
    {
        echo view('some_view');
    }
}
