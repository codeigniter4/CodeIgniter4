<?php

namespace Config;

use CodeIgniter\Autoloader\FileLocator;
use CodeIgniter\Autoloader\FileLocatorCached;
use CodeIgniter\Config\BaseService;

class Services extends BaseService
{
    // ...

    public static function locator(bool $getShared = true)
    {
        if ($getShared) {
            if (! isset(static::$instances['locator'])) {
                static::$instances['locator'] = new FileLocatorCached(new FileLocator(static::autoloader()));
            }

            return static::$mocks['locator'] ?? static::$instances['locator'];
        }

        return new FileLocator(static::autoloader());
    }
}
