<?php

$data = [
    'username' => 'darth',
    'email'    => 'd.vader@theempire.com',
];

// Inserts data and returns inserted row's primary key
$userModel->insert($data);

// Inserts data and returns true on success and false on failure
$userModel->insert($data, false);

// Returns inserted row's primary key
$userModel->getInsertID();
