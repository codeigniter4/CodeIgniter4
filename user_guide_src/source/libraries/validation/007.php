<?php

$validation->setRules([
    'username' => ['label' => 'Username', 'rules' => 'required|max_length[30]'],
    'password' => ['label' => 'Password', 'rules' => 'required|max_length[255]|min_length[10]'],
]);
// or
$validation->setRules([
    'username' => ['label' => 'Username', 'rules' => 'required|max_length[30]'],
    'password' => ['label' => 'Password', 'rules' => ['required', 'max_length[255]', 'min_length[10]']],
]);
