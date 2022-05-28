<?php

$union   = $this->db->table('users')->select('id', 'name');
$builder = $this->db->table('users')->select('id', 'name');

$builder->union($union)->limit(10)->get();
/*
 * Produces:
 * SELECT * FROM (SELECT `id`, `name` FROM `users` LIMIT 10) uwrp0
 * UNION SELECT * FROM (SELECT `id`, `name` FROM `users`) uwrp1
 */
