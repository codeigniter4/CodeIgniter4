<?php

$data = [
    'title' => 'My title',
    'name'  => 'My Name',
    'date'  => 'My date',
];

$sql = $builder->set($data)->getCompiledInsert();
echo $sql;
// Produces string: INSERT INTO mytable (`title`, `name`, `date`) VALUES ('My title', 'My name', 'My date')
