<?php

namespace App\Controllers;

use App\Attributes\AddHeader;
use CodeIgniter\Controller;
use CodeIgniter\Router\Attributes\Cache;

class Api extends Controller
{
    /**
     * Add a single custom header
     */
    #[AddHeader('X-API-Version', '2.0')]
    public function userInfo()
    {
        return $this->response->setJSON([
            'name'  => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }

    /**
     * Add multiple custom headers using the IS_REPEATABLE attribute option.
     * Each AddHeader attribute will be executed in order.
     */
    #[AddHeader('X-API-Version', '2.0')]
    #[AddHeader('X-Rate-Limit', '100')]
    #[AddHeader('X-Content-Source', 'cache')]
    public function statistics()
    {
        return $this->response->setJSON([
            'users' => 1500,
            'posts' => 3200,
        ]);
    }

    /**
     * Combine custom attributes with built-in attributes.
     * The Cache attribute will cache the response,
     * and AddHeader will add the custom header.
     */
    #[AddHeader('X-Powered-By', 'My Custom API')]
    #[Cache(for: 3600)]
    public function dashboard()
    {
        return $this->response->setJSON([
            'status' => 'operational',
            'uptime' => '99.9%',
        ]);
    }
}
