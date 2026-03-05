<?php

namespace App\Requests;

use CodeIgniter\HTTP\FormRequest;
use CodeIgniter\HTTP\ResponseInterface;

class StorePostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|min_length[3]',
            'body'  => 'required',
        ];
    }

    // Always respond with JSON, regardless of the request type.
    protected function failedValidation(array $errors): ResponseInterface
    {
        return service('response')->setStatusCode(422)->setJSON(['errors' => $errors]);
    }

    // Always respond with 403, regardless of the request type.
    protected function failedAuthorization(): ResponseInterface
    {
        return service('response')->setStatusCode(403);
    }
}
