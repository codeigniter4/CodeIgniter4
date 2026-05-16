<?php

namespace App\Controllers;

use App\Requests\StorePostRequest;

class Posts extends BaseController
{
    public function store(StorePostRequest $request): string
    {
        $data  = $request->getValidated();
        $files = $this->request->getFiles();

        // ...
    }
}
