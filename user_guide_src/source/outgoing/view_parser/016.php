<?php

public $plugins = [
    'foo' => '\Some\Class::methodName'
];

// Tag is replaced by the return value of Some\Class::methodName static function.
{+ foo +}
