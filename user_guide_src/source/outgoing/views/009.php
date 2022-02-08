<?php

namespace App\Controllers;

class Blog extends \CodeIgniter\Controller
{
    public function index()
    {
        $data['title']   = "My Real Title";
        $data['heading'] = "My Real Heading";

        echo view('blog_view', $data);
    }
}
