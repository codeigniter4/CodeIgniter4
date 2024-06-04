<?php

namespace CustomModule\Config;

use Config\Validation;

class ModuleValidation extends Validation
{
    public array $rule = [];

    // ...

}


namespace App\Controllers;

use CustomModule\Config\ModuleValidation;

class Helloworld extends BaseController
{
    public function index()
    {
        $this->setConfigValidator(config(ModuleValidation::class));
    }
}
