<?php

namespace Config;

use CodeIgniter\Config\View as BaseView;

class View extends BaseView
{
    public $filters = [
        'foo'        => '\Some\Class::methodName',
        'str_repeat' => 'str_repeat', // native php function
    ];

    // ...
}
