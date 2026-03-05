<?php

namespace App\Controllers;

use App\Requests\UpdatePostRequest;

class Posts extends BaseController
{
    // Route parameters come first; FormRequest follows.
    public function update(int $id, UpdatePostRequest $request): string
    {
        $data = $request->validated();

        // update post $id with $data

        return redirect()->to('/posts/' . $id);
    }
}
