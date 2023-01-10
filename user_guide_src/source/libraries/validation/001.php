<?php

namespace App\Controllers;

use Config\Services;

class Form extends BaseController
{
    protected $helpers = ['form'];

    public function index()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return view('signup');
        }

        $rules = [];

        if (! $this->validate($rules)) {
            return view('signup');
        }

        return view('success');
    }
}
