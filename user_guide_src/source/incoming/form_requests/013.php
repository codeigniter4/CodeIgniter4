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

    // Override so that old() reflects the normalized values on redirect.
    protected function failedValidation(array $errors): ResponseInterface
    {
        if ($this->request->is('json') || $this->request->isAJAX()) {
            return service('response')->setStatusCode(422)->setJSON(['errors' => $errors]);
        }

        // withInput() flashes the original superglobals and the validation
        // errors. We then overwrite old input with the normalized payload so
        // that old() returns the same values that were validated.
        $redirect = redirect()->back()->withInput();

        service('session')->setFlashdata('_ci_old_input', [
            'get'  => [],
            'post' => $this->prepareForValidation($this->validationData()),
        ]);

        return $redirect;
    }
}
