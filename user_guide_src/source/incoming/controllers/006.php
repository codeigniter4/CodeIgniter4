<?php

namespace App\Controllers;

class Helloworld extends BaseController
{
    public function index()
    {
        echo 'Hello World!';
    }

    public function comment()
    {
        echo 'I am not flat!';
    }
}
