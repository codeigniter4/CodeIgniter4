<?php

namespace App\Controllers;

class Blog extends BaseController
{
    public function index()
    {
        $data['title']   = 'My Real Title';
        $data['heading'] = 'My Real Heading';

        return view('blog_view', $data);
    }
}
