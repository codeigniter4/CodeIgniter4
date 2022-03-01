<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Page extends Controller
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
