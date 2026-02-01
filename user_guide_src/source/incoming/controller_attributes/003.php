<?php

namespace App\Controllers;

use CodeIgniter\Router\Attributes\Filter;

class HomeController extends BaseController
{
    // Apply the filter by it's alias name
    #[Filter(by: 'csrf')]
    public function index()
    {
    }

    // Apply a filter with arguments
    #[Filter(by: 'throttle', having: ['60', '1'])]
    public function api()
    {
    }

    // Multiple filters can be applied
    #[Filter(by: ['auth', 'csrf'])]
    public function admin()
    {
    }
}
