<?php

namespace App\Controllers;

class Helloworld extends BaseController
{
    public function index($name)
    {
        echo 'Hello ' . esc($name) . '!';
    }
}
