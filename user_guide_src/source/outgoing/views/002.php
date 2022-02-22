<?php

namespace App\Controllers;

class Blog extends \CodeIgniter\Controller
{
    public function index()
    {
        echo view('blog_view');
    }
}
