<?php

namespace App\Controllers;

use App\Requests\UpdatePostRequest;
use CodeIgniter\HTTP\ResponseInterface;

class Posts extends BaseController
{
    public function _remap(string $method, string ...$params): mixed
    {
        // FormRequest is not injected automatically into _remap().
        // Instantiate it manually and call resolveRequest() yourself.
        if ($method === 'update') {
            $request  = new UpdatePostRequest();
            $response = $request->resolveRequest();

            if ($response instanceof ResponseInterface) {
                // Authorization or validation failed - send the response.
                return $response;
            }

            return $this->update($request, ...$params);
        }

        return $this->{$method}(...$params);
    }

    private function update(UpdatePostRequest $request, string $id): string
    {
        $data = $request->validated();

        // update post $id with $data

        return redirect()->to('/posts/' . $id);
    }
}
