<?php

$sql = $builder->getCompiledSelect();
echo $sql;
// Prints string: SELECT * FROM mytable
