<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Form extends Controller
{
    public function index()
    {
        helper('form');

        $data = $this->request->getPost();

        if (! $this->validateData($data, [
            // Validation rules
        ])) {
            return view('myform');
        }

        return view('formsuccess');
    }
}
