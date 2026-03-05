<?php

namespace App\Requests;

use CodeIgniter\HTTP\FormRequest;

class StorePostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|min_length[3]|max_length[255]',
            'body'  => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'title' => [
                'required'   => 'Post title cannot be empty.',
                'min_length' => 'Post title must be at least {param} characters long.',
            ],
            'body' => [
                'required' => 'Post body cannot be empty.',
            ],
        ];
    }
}
