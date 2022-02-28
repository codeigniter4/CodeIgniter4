<?php

class View extends BaseView
{
   'foo' => '\Some\Class::methodName',
    public $plugins = [
        'bar' => function($str, array $params = []) {
            return $str;
        },
    ];
}
