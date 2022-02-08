<?php

$criteria = [
    'email'  => 'joe@example.com',
    'active' => 1,
];
$this->dontSeeInDatabase('users', $criteria);
