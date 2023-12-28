<?php

namespace App\Controllers;

class Catalog extends BaseController
{
    public function productLookup(...$params)
    {
        echo $params[0] ?? null; // Will be 123 in all examples
        echo $params[1] ?? null; // null in first, 456 in second and third example
        echo $params[2] ?? null; // null in first and second, 789 in third
    }
}
