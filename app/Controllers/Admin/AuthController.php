<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AdminModel;

class AuthController extends BaseController
{
    public function login()
    {
        if (session()->get('is_admin_logged_in')) {
            return redirect()->to('/admin/dashboard');
        }
        return view('admin/login');
    }

    public function attemptLogin()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $model = new AdminModel();
        $admin = $model->where('username', $username)->first();

        if ($admin) {
            if (password_verify($password, $admin['password'])) {
                $sessionData = [
                    'admin_id'           => $admin['id'],
                    'admin_name'         => $admin['name'],
                    'admin_username'     => $admin['username'],
                    'is_admin_logged_in' => true,
                ];
                session()->set($sessionData);
                return redirect()->to('/admin/dashboard');
            }
        }

        return redirect()->back()->withInput()->with('error', 'نام کاربری یا رمز عبور اشتباه است.');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/admin/login');
    }
}
