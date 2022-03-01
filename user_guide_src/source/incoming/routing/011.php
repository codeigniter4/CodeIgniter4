<?php

namespace App\Controllers;

class ProductController extends BaseController
{
    public function productLookup($seg1 = false, $seg2 = false, $seg3 = false)
    {
        echo $seg1; // Will be 123 in all examples
        echo $seg2; // false in first, 456 in second and third example
        echo $seg3; // false in first and second, 789 in third
    }
}
