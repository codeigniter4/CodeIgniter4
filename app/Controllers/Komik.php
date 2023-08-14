<?php

namespace App\Controllers;

class Komik extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Home | landing page'
        ];

        return view('komik/index', $data);
    }
}
