<?php

$subquery = $db->table('users')->select('id, name');
$builder  = $db->newQuery()->fromSubquery($subquery, 't');
$query    = $builder->get();
// Produces: SELECT * FROM (SELECT `id`, `name` FROM users) `t`
