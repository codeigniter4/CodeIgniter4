<?php

$validation->setRules([
    'username' => 'required',
    'password' => 'required|min_length[10]',
]);
// or
$validation->setRules([
    'username' => 'required',
    'password' => ['required', 'min_length[10]'],
]);
