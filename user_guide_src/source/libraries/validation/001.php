<?php

namespace App\Controllers;

use Config\Services;

class Form extends BaseController
{
    protected $helpers = ['form'];

    public function index()
    {
        if (! $this->request->is('post')) {
            return view('signup');
        }

        $rules = [];

        if (! $this->validate($rules)) {
            return view('signup');
        }

        return view('success');
    }
}
