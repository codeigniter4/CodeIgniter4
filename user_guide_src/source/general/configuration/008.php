<?php

namespace Config;

use CodeIgniter\Config\BaseService;

class MyConfig extends BaseConfig
{
    public static $registrars = [
        SupportingPackageRegistrar::class,
    ];
}
