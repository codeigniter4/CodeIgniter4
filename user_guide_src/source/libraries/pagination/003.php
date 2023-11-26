<?php

// In your Controller.
$model = new \App\Models\UserModel();

$data = [
    'users' => $model->where('ban', 1)->paginate(10),
    'pager' => $model->pager,
];
