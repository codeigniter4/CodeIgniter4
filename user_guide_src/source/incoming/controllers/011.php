<?php

namespace App\Controllers;

class Products extends BaseController
{
    public function _remap($method)
    {
        if ($method === 'some_method') {
            return $this->{$method}();
        }

        return $this->default_method();
    }
}
