<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use Throwable;

class Migrate extends Controller
{
    public function index()
    {
        $migrate = \Config\Services::migrations();

        try {
            $migrate->latest();
        } catch (Throwable $e) {
            // Do something with the error here...
        }
    }
}
