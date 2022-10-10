<?php

$data = [
    'username' => 'darth',
    'email'    => 'd.vader@theempire.com',
];

$userModel->insert($data, bool $returnID = true);
