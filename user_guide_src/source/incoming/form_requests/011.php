<?php

use App\Requests\StorePostRequest;
use App\Requests\UpdatePostRequest;

// In app/Config/Routes.php

// FormRequest only - no route parameters.
$routes->post('posts', static function (StorePostRequest $request): string {
    $data = $request->validated();

    // save to database

    return redirect()->to('/posts');
});

// Route parameter before a FormRequest - same convention as controller methods.
$routes->post('posts/(:num)', static function (int $id, UpdatePostRequest $request): string {
    $data = $request->validated();

    // update post $id with $data

    return redirect()->to('/posts/' . $id);
});
