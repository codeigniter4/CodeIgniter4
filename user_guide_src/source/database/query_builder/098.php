<?php

// Note that the second parameter of the ``get_compiled_select`` method is false
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
 * SELECT field1, field1 from mytable where field3 = 5;
 */
