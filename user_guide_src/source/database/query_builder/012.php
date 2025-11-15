<?php

$builder->selectAvg('age');
$query = $builder->get();
// Produces: SELECT AVG(age) as age FROM mytable
