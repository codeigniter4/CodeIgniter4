<?php

namespace App\Controllers;

use App\Requests\UpdatePostRequest;

class Posts extends BaseController
{
    // Route parameters come first; FormRequest follows.
    public function update(int $id, UpdatePostRequest $request): string
    {
        $data = $request->getValidated();

        // update post $id with $data

        return redirect()->to('/posts/' . $id);
    }
}
