<?php

$pQuery = $db->prepare(static function ($db) {
    return $db->table('user')->insert([
        'name'    => 'x',
        'email'   => 'y',
        'country' => 'US',
    ]);
});
