<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Form extends Controller
{
    public function index()
    {
        helper(['form', 'url']);

        if (! $this->validate([
            // Validation rules
        ])) {
            echo view('myform');
        } else {
            echo view('formsuccess');
        }
    }
}
