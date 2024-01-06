<?php

$userModel = new \App\Models\UserModel();
$page      = 3;

$users = $userModel->paginate(10, 'group1', $page);
