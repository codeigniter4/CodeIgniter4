<?php

$criteria = [
    'email'  => 'joe@example.com',
    'active' => 1,
];
$this->seeInDatabase('users', $criteria);
