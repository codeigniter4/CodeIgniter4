<?php

namespace App\Libraries;

use CodeIgniter\Autoloader\FileLocatorInterface;
use CodeIgniter\Router\RouteCollection as BaseRouteCollection;
use Config\Modules;
use Config\Routing;

class RouteCollection extends BaseRouteCollection
{
    public function __construct(FileLocatorInterface $locator, Modules $moduleConfig, Routing $routing)
    {
        parent::__construct($locator, $moduleConfig, $routing);

        // your code here
    }
}
