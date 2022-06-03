<?php

namespace Config;

use CodeIgniter\Config\BaseService;

class Services extends BaseService
{
    public static function routes($getShared = false)
    {
        if (! $getShared) {
            return new \CodeIgniter\Router\RouteCollection();
        }

        return static::getSharedInstance('routes');
    }

    // ...
}
