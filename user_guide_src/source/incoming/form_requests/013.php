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
            'slug'  => 'required|is_unique[posts.slug]',
        ];
    }

    protected function prepareForValidation(array $data): array
    {
        $data['slug'] = url_title($data['title'] ?? '', '-', true);

        return $data;
    }

    // Override while still flashing the prepared values on redirect.
    protected function failedValidation(array $errors, array $preparedData): ResponseInterface
    {
        if (
            $this->request->is('json')
            || $this->request->negotiate('media', ['text/html', 'application/json'], true) === 'application/json'
        ) {
            return service('response')->setStatusCode(422)->setJSON(['errors' => $errors]);
        }

        // withInput() flashes validation errors. Then we replace old input with
        // the same prepared values that were passed to validation.
        $redirect = redirect()->back()->withInput();

        service('session')->setFlashdata('_ci_old_input', [
            'get'  => [],
            'post' => $preparedData,
        ]);

        return $redirect;
    }
}
