<?php

namespace App\Controllers;

class UserController extends \CodeIgniter\Controller
{
    public function index()
    {
        $locale = $this->request->getLocale();
    }
}
