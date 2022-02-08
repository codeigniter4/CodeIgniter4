<?php

namespace App\Controllers;

class Page extends \CodeIgniter\Controller
{
    public function index()
    {
        $data = [
            'page_title' => 'Your title',
        ];

        echo view('header');
        echo view('menu');
        echo view('content', $data);
        echo view('footer');
    }
}
