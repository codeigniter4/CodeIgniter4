<?php

$union   = $db->table('users')->select('id, name')->orderBy('id', 'DESC')->limit(5);
$builder = $db->table('users')->select('id, name')->orderBy('id', 'ASC')->limit(5)->union($union);

$db->newQuery()->fromSubquery($builder, 'q')->orderBy('id', 'DESC')->get();
/*
 * Produces:
 * SELECT * FROM (
 *      SELECT * FROM (SELECT `id`, `name` FROM `users` ORDER BY `id` ASC LIMIT 5) uwrp0
 *      UNION
 *      SELECT * FROM (SELECT `id`, `name` FROM `users` ORDER BY `id` DESC LIMIT 5) uwrp1
 * ) q ORDER BY `id` DESC
 */
