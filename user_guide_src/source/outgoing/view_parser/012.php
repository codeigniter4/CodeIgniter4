<?php

namespace Config;

use CodeIgniter\Config\View as BaseView;

class View extends BaseView
{
    public $filters = [
        'abs'        => '\CodeIgniter\View\Filters::abs',
        'capitalize' => '\CodeIgniter\View\Filters::capitalize',
    ];

    // ...
}
