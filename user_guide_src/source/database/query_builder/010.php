<?php

$builder->selectMax('age');
$query = $builder->get();
// Produces: SELECT MAX(age) as age FROM mytable

$builder->selectMax('age', 'member_age');
$query = $builder->get();
// Produces: SELECT MAX(age) as member_age FROM mytable
