<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Blog extends Controller
{
    public function index()
    {
        echo view('blog_view');
    }
}
