<?php

namespace Config;

use CodeIgniter\Config\View as BaseView;

class View extends BaseView
{
    public array $decorators = [
        'App\Views\Decorators\MyDecorator',
    ];

    // ...
}
