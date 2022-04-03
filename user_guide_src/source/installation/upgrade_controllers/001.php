<?php

namespace App\Controllers;

class Helloworld extends BaseController
{
    public function index($name)
    {
        return 'Hello ' . esc($name) . '!';
    }
}
