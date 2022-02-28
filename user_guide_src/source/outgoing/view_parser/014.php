<?php

class View extends BaseView
{

    public $plugins = [
        'foo' => '\Some\Class::methodName',
        'bar' => function($str, array $params = []) {
            return $str;
        },
    ];
}
