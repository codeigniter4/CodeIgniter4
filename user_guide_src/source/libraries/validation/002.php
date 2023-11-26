<?php

// ...

$rules = [
    'username' => 'required|max_length[30]',
    'password' => 'required|max_length[255]|min_length[10]',
    'passconf' => 'required|max_length[255]|matches[password]',
    'email'    => 'required|max_length[254]|valid_email',
];

// ...
