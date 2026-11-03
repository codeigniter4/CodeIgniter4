<?php

namespace App\Controllers;

use CodeIgniter\Router\Attributes\Restrict;

// Restrict access by environment
#[Restrict(environment: ['development', '!production'])]
class HomeController extends BaseController
{
    // Restrict access by hostname
    #[Restrict(hostname: 'localhost')]
    public function index()
    {
    }

    // Multiple allowed hosts
    #[Restrict(hostname: ['localhost', '127.0.0.1', 'dev.example.com'])]
    public function devOnly()
    {
    }

    // Restrict to subdomain, e.g. admin.example.com
    #[Restrict(subdomain: 'admin')]
    public function deleteItem($id)
    {
    }
}
