<?php

public $plugins = [
    'foo' => '\Some\Class::methodName',
    'bar' => function ($str, array $params=[]) {
        return $str;
    },
];
