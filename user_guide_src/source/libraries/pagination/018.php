<?php

// In your Controller.
$model = new \App\Models\UserModel();

$data = [
    'users' => $model->banned()->paginate(10),
    'pager' => $model->pager,
];
