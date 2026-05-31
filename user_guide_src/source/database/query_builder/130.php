<?php

$data = [
    'title' => 'My title',
    'name'  => 'My Name',
    'date'  => '2022-01-01',
];

$insertID = $builder->insertGetID($data);
