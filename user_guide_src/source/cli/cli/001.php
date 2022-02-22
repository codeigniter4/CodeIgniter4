<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Tools extends Controller
{
    public function message($to = 'World')
    {
        echo "Hello {$to}!" . PHP_EOL;
    }
}
