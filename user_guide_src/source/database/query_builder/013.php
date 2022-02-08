<?php

$builder->selectSum('age');
$query = $builder->get();
// Produces: SELECT SUM(age) as age FROM mytable
