<?php

$builder = $db->table('mytable');
$query   = $builder->get();  // Produces: SELECT * FROM mytable
