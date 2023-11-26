<?php

namespace App\Controllers;

class Products extends BaseController
{
    public function getShoes($sandals, $id)
    {
        return $sandals . $id;
    }
}
