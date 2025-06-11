<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Login extends BaseController
{
    public function index()
    {
        if (session()->get('is_logged_in')) {
            return redirect()->to('/chat');
        }

        helper(['form']);
        return view('login/index', ['validation' => $this->validator]);
    }

    public function authenticate()
    {
        if (session()->get('is_logged_in')) {
            return redirect()->to('/chat');
        }

        helper(['form']);
        $rules = [
            'password' => 'required',
        ];

        if (! $this->validate($rules)) {
            // Pass validation errors to the view via session flash data or directly if rendering view
            // For redirect, flash data is better.
            session()->setFlashdata('validation_errors', $this->validator->getErrors());
            return redirect()->to('/login')->withInput();
        }

        $password = $this->request->getPost('password');

        if ($password === '4455') {
            session()->set('is_logged_in', true);
            return redirect()->to('/chat');
        } else {
            session()->setFlashdata('error', 'رمز عبور وارد شده صحیح نمی باشد.'); // "Incorrect password"
            return redirect()->to('/login')->withInput();
        }
    }
}
