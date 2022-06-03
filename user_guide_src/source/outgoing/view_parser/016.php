<?php

namespace Config;

use CodeIgniter\Config\View as BaseView;

class View extends BaseView
{
    public $plugins = [
        'foo' => '\Some\Class::methodName',
    ];

    // ...
}

/*
 * Tag is replaced by the return value of Some\Class::methodName() static function.
 * {+ foo +}
 */
