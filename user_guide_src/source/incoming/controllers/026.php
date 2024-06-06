<?php

namespace Acme\Config;

use Config\Validation;

class ModuleValidation extends Validation
{
    public array $rule = [];

    // ...
}

namespace App\Controllers;

use Acme\Config\ModuleValidation;

class Helloworld extends BaseController
{
    public function index()
    {
        $this->configValidation = config(ModuleValidation::class);
    }
}
