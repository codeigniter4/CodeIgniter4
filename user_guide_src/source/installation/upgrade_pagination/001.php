<?php

$model = new \App\Models\UserModel();

$data = [
    'users' => $model->paginate(10),
    'pager' => $model->pager,
];

echo view('users/index', $data);
