<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourcePresenter;

class Photos extends ResourcePresenter
{
    protected $modelName = 'App\Models\Photos';

    public function index()
    {
        return view('templates/list', $this->model->findAll());
    }

    // ...
}
