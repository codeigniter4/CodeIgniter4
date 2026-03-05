<?php

namespace App\Controllers;

use App\Requests\StorePostRequest;

class Posts extends BaseController
{
    public function store(StorePostRequest $request): string
    {
        // $request->validated() returns only the fields declared in rules().
        $data = $request->validated();

        // save to database

        return redirect()->to('/posts');
    }
}
