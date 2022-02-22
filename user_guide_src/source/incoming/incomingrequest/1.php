<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class UserController extends Controller
{
    public function index()
    {
        if ($this->request->isAJAX()) {
            // ...
        }
    }
}
