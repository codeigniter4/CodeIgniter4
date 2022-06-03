<?php

namespace App\Views\Decorators;

use CodeIgniter\Views\ViewDecoratorInterface;

class MyDecorator implements ViewDecoratorInterface
{
    public static function decorate(string $html): string
    {
        // Modify the output here

        return $html;
    }
}
