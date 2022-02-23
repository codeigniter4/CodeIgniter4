<?php

$validation->setRules([
    'username' => ['label' => 'Username', 'rules' => 'required'],
    'password' => ['label' => 'Password', 'rules' => 'required|min_length[10]'],
]);
