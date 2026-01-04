<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AdminModel;

class AdminsController extends BaseController
{
    public function index()
    {
        $model = new AdminModel();
        $admins = $model->findAll();
        return view('admin/admins/index', ['admins' => $admins]);
    }

    public function create()
    {
        $model = new AdminModel();
        $data = [
            'name'     => $this->request->getPost('name'),
            'username' => $this->request->getPost('username'),
            'password' => $this->request->getPost('password'),
        ];

        if ($model->save($data)) {
            return redirect()->to('/admin/admins')->with('success', 'مدیر جدید با موفقیت اضافه شد.');
        } else {
            return redirect()->back()->with('error', 'خطا در افزودن مدیر. لطفا ورودی‌ها را بررسی کنید.')->withInput();
        }
    }

    public function update($id)
    {
        $model = new AdminModel();
        $data = [
            'id'       => $id,
            'name'     => $this->request->getPost('name'),
            'username' => $this->request->getPost('username'),
        ];

        // Only update password if provided
        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $data['password'] = $password;
        }

        if ($model->save($data)) {
            return redirect()->to('/admin/admins')->with('success', 'اطلاعات مدیر با موفقیت ویرایش شد.');
        } else {
            return redirect()->back()->with('error', 'خطا در ویرایش مدیر.')->withInput();
        }
    }
}
