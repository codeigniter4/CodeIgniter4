<?php

echo $builder->limit(10, 20)->getCompiledSelect(false);
/*
 * Prints string: SELECT * FROM mytable LIMIT 20, 10
 * (in MySQL. Other databases have slightly different syntax)
 */

echo $builder->select('title, content, date')->getCompiledSelect();
// Prints string: SELECT title, content, date FROM mytable LIMIT 20, 10
