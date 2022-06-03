<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class UserController extends Controller
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
