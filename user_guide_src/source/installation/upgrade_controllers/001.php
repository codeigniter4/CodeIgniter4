<?php

class Helloworld extends CI_Controller
{
    public function index($name)
    {
        echo "Hello $name! ";
    }
}
