<?php

$subquery = $db->table('countries')->select('name')->where('id', 1);
$builder  = $db->table('users')->select('name')->selectSubquery($subquery, 'country');
$query    = $builder->get();
// Produces: SELECT `name`, (SELECT `name` FROM `countries` WHERE `id` = 1) `country` FROM `users`
