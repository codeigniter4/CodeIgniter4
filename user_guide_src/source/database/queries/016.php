<?php

$pQuery = $db->prepare(function ($db) {
    return $db->table('user')->insert([
        'name'    => 'x',
        'email'   => 'y',
        'country' => 'US'
    ]);
});
