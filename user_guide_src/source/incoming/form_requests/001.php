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
}
