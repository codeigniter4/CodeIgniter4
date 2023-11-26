<?php

$list = [
    'red',
    'blue',
    'green',
    'yellow',
];

$attributes = [
    'class' => 'boldlist',
    'id'    => 'mylist',
];

echo ul($list, $attributes);
