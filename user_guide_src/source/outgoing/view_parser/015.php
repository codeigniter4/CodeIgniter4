<?php

class View extends \CodeIgniter\Config\View
{
    public $plugins = [];

    public function __construct()
    {
        $this->plugins['bar'] = function (array $params=[]) {
            return $params[0] ?? '';
        };

        parent::__construct();
    }
}
