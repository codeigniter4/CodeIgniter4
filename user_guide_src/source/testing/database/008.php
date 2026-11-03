<?php

$criteria = [
    'active' => 1,
];
$this->seeNumRecords(2, 'users', $criteria);
