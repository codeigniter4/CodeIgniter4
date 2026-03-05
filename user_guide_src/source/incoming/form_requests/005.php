<?php

namespace App\Requests;

use CodeIgniter\HTTP\FormRequest;

class StorePostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|min_length[3]',
            'body'  => 'required',
        ];
    }

    public function authorize(): bool
    {
        // Only authenticated users may submit posts.
        return auth()->loggedIn();
    }
}
