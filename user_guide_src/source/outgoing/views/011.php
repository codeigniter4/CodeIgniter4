<?php

namespace App\Controllers;

class Blog extends \CodeIgniter\Controller
{
    public function index()
    {
        $data = [
            'todo_list' => ['Clean House', 'Call Mom', 'Run Errands'],
            'title'     => 'My Real Title',
            'heading'   => 'My Real Heading',
        ];

        echo view('blog_view', $data);
    }
}
