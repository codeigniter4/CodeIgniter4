<?php

use App\Models\UserModel;

$userModel = model(UserModel::class);
$page      = 3;

$users = $userModel->paginate(10, 'group1', $page);
