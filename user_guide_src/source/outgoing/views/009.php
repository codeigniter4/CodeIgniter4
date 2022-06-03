<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Blog extends Controller
{
    public function index()
    {
        $data['title']   = 'My Real Title';
        $data['heading'] = 'My Real Heading';

        return view('blog_view', $data);
    }
}
