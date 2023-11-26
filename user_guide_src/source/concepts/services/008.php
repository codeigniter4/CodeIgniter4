<?php

namespace Config;

use CodeIgniter\Config\BaseService;

class Services extends BaseService
{
    // ...

    public static function renderer($viewPath = APPPATH . 'views/')
    {
        return new \CodeIgniter\View\View($viewPath);
    }
}
