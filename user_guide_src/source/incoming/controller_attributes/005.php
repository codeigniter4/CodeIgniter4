<?php

namespace App\Controllers;

use CodeIgniter\Router\Attributes\Cache;

class HomeController extends BaseController
{
    // Cache this method's response for 2 hours
    #[Cache(for: 2 * HOUR)]
    public function index()
    {
        return view('welcome_message');
    }

    // Custom cache key
    #[Cache(for: 10 * MINUTE, key: 'custom_cache_key')]
    public function custom()
    {
        return 'This response is cached with a custom key for 10 minutes.';
    }
}
