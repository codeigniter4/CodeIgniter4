<?php

use App\Models\UserModel;

// In your Controller.
$model = model(UserModel::class);

$data = [
    'users' => $model->banned()->paginate(10),
    'pager' => $model->pager,
];
