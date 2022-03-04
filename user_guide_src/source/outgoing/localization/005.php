<?php

namespace App\Controllers;

class UserController extends \Controller
{
    public function index()
    {
        $locale = $this->request->getLocale();
    }
}
