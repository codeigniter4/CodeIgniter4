<?php

namespace Config;

use CodeIgniter\Config\View as BaseView;

class View extends BaseView
{
    public $plugins = [];

    public function __construct()
    {
        $this->plugins['bar'] = static fn (array $params = []) => $params[0] ?? '';

        parent::__construct();
    }

    // ...
}
