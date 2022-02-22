<?php

namespace App\Controllers;

class Products extends BaseController
{
    public function shoes($sandals, $id)
    {
        echo $sandals;
        echo $id;
    }
}
