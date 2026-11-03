<?php

namespace App\Controllers;

use CodeIgniter\Router\Attributes\Filter;

#[Filter(by: 'auth')]
class AdminController extends BaseController
{
    public function index()
    {
        return view('welcome_message');
    }
}
