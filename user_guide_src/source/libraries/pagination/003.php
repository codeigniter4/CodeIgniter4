<?php

use App\Models\UserModel;

// In your Controller.
$model = model(UserModel::class);

$data = [
    'users' => $model->where('ban', 1)->paginate(10),
    'pager' => $model->pager,
];
