<?php

namespace App\Controllers;

class UserController extends BaseController
{
    public function index()
    {
        $model = new \App\Models\UserModel();

        $data = [
            'users' => $model->paginate(10),
            'pager' => $model->pager,
        ];

        return view('users/index', $data);
    }
}
