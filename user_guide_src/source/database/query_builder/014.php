<?php

$builder->selectCount('age');
$query = $builder->get();
// Produces: SELECT COUNT(age) as age FROM mytable
