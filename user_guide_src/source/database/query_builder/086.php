<?php

$array = [
    'name'   => $name,
    'title'  => $title,
    'status' => $status,
];

$builder->set($array);
$builder->insert();
