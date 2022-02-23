<?php

$formatters = [
    'first'  => 'firstName',
    'email'  => 'email',
    'phone'  => 'phoneNumber',
    'avatar' => 'imageUrl',
];

$fabricator = new Fabricator(UserModel::class, $formatters);
