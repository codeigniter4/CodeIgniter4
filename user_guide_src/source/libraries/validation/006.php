<?php

$validation->setRules([
    'username' => 'required|max_length[30]',
    'password' => 'required|max_length[255]|min_length[10]',
]);
// or
$validation->setRules([
    'username' => ['required', 'max_length[30]'],
    'password' => ['required', 'max_length[255]', 'min_length[10]'],
]);
