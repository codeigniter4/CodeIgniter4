<?php

namespace App\Requests;

use CodeIgniter\HTTP\FormRequest;

class StorePostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|min_length[3]',
            'slug'  => 'required|is_unique[posts.slug]',
        ];
    }

    protected function prepareForValidation(array $data): array
    {
        // Derive the slug from the title so it is available for the
        // is_unique rule before validation runs.
        $data['slug'] = url_title($data['title'] ?? '', '-', true);

        return $data;
    }
}
