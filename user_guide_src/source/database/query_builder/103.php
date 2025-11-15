<?php

$builder = $db->table('users')->select('id, name')->limit(10);
$union   = $db->table('groups')->select('id, name');
$builder->union($union)->get();
/*
 * Produces:
 * SELECT * FROM (SELECT `id`, `name` FROM `users` LIMIT 10) uwrp0
 * UNION SELECT * FROM (SELECT `id`, `name` FROM `groups`) uwrp1
 */
