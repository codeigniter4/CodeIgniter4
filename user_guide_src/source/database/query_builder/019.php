<?php

$builder = $db->table('blogs');
$builder->select('*');
$builder->join('comments', 'comments.id = blogs.id');
$query = $builder->get();
/*
 * Produces:
 * SELECT * FROM blogs JOIN comments ON comments.id = blogs.id
 */
