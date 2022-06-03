<?php

// Prepare the Query
$pQuery = $db->prepare(static function ($db) {
    return $db->table('user')->insert([
        'name'    => 'x',
        'email'   => 'y',
        'country' => 'US',
    ]);
});

// Collect the Data
$name    = 'John Doe';
$email   = 'j.doe@example.com';
$country = 'US';

// Run the Query
$results = $pQuery->execute($name, $email, $country);
