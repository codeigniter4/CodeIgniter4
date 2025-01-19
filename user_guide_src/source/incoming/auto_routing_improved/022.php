<?php

namespace App\Controllers;

class Products extends BaseController
{
    public function getShoes($type, $id)
    {
        return $type . $id;
    }
}
