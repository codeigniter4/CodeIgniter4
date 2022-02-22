<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Form extends Controller
{
    public function index()
    {
        helper(['form', 'url']);

        if (! $this->validate([])) {
            echo view('Signup', [
                'validation' => $this->validator,
            ]);
        } else {
            echo view('Success');
        }
    }
}
