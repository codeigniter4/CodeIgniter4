<?php

$user = $userModel->find($id);

// Display
echo $user->username;
echo $user->email;

// Updating
unset($user->username);

if (! isset($user->username)) {
    $user->username = 'something new';
}

$userModel->save($user);

// Create
$user           = new \App\Entities\User();
$user->username = 'foo';
$user->email    = 'foo@example.com';
$userModel->save($user);
