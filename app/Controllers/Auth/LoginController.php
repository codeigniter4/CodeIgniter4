<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\UserModel;

class LoginController extends BaseController
{
    public function index()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }
        return view('auth/login');
    }

    public function login()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (!$this->validate($rules)) {
            return view('auth/login', [
                'validation' => $this->validator,
            ]);
        }

        $email = $this->request->getVar('email');
        $password = $this->request->getVar('password');

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();

        if ($user) {
            $pass = $user['password'];
            if (password_verify($password, $pass)) {
                $ses_data = [
                    'id'       => $user['id'],
                    'name'     => $user['name'],
                    'email'    => $user['email'],
                    'role'     => $user['role'],
                    'isLoggedIn' => true,
                ];
                session()->set($ses_data);
                return redirect()->to('/dashboard');
            }
        }

        session()->setFlashdata('error', 'ایمیل یا رمز عبور اشتباه است.');
        return redirect()->to('/login');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
