<?php

namespace App\Controllers;

use App\Requests\StorePostRequest;

class Posts extends BaseController
{
    public function store(StorePostRequest $request): string
    {
        // $request->getValidated() returns only the fields declared in rules().
        $data = $request->getValidated();

        // save to database

        return redirect()->to('/posts');
    }
}
