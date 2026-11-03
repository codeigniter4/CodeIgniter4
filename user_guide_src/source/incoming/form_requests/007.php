<?php

namespace App\Requests;

use CodeIgniter\HTTP\FormRequest;

class SearchRequest extends FormRequest
{
    public function rules(): array
    {
        return ['q' => 'required|min_length[2]'];
    }

    // Merge query-string parameters and POST body for this endpoint.
    protected function validationData(): array
    {
        return array_merge(
            $this->request->getGet() ?? [],
            $this->request->getPost() ?? [],
        );
    }
}
