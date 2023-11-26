<?php

namespace App\Controllers;

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

        // If you want to get the validated data.
        $validData = $this->validator->getValidated();

        return view('success');
    }
}
