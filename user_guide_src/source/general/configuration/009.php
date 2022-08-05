<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class MySalesConfig extends BaseConfig
{
    public $target            = 100;
    public $campaign          = 'Winter Wonderland';
    public static $registrars = [
        '\App\Models\RegionalSales',
    ];
}
