<?php

// Note that the parameter of the `getCompiledSelect()` method is false
$sql = $builder->select(['field1', 'field2'])
    ->where('field3', 5)
    ->getCompiledSelect(false);

// ...
// Do something crazy with the SQL code... like add it to a cron script for
// later execution or something...
// ...

$data = $builder->get()->getResultArray();
/*
 * Would execute and return an array of results of the following query:
 * SELECT field1, field2 FROM mytable WHERE field3 = 5;
 */
