<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\UserModel;

class RegisterController extends BaseController
{
    public function index()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }
        return view('auth/register');
    }

    public function register()
    {
        $rules = [
            'name'     => 'required|min_length[3]|max_length[100]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]|max_length[255]',
            'confirm_password' => 'matches[password]',
        ];

        if (!$this->validate($rules)) {
            return view('auth/register', [
                'validation' => $this->validator,
            ]);
        }

        $userModel = new UserModel();
        $newData = [
            'name'     => $this->request->getVar('name'),
            'email'    => $this->request->getVar('email'),
            'password' => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT),
            'role'     => 'user',
        ];

        $userModel->save($newData);

        // Auto login after register
        $user = $userModel->where('email', $newData['email'])->first();
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
