<?php

$builder->selectMin('age');
$query = $builder->get();
// Produces: SELECT MIN(age) as age FROM mytable
