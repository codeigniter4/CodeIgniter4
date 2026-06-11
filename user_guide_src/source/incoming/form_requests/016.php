<?php

namespace App\Requests;

use CodeIgniter\HTTP\FormRequest;
use CodeIgniter\HTTP\ResponseInterface;

class StorePostRequest extends FormRequest
{
    /**
     * @var array<string, string>
     */
    public array $errors = [];

    /**
     * @var array<string, mixed>
     */
    public array $form = [];

    public function rules(): array
    {
        return [
            'title' => 'required|min_length[3]',
            'body'  => 'required',
        ];
    }

    protected function failedValidation(array $errors, array $preparedData): ?ResponseInterface
    {
        $this->errors = $errors;
        $this->form   = $preparedData;

        return null;
    }
}

namespace App\Controllers;

use App\Requests\StorePostRequest;
use CodeIgniter\HTTP\ResponseInterface;

class Posts extends BaseController
{
    public function create(StorePostRequest $request): ResponseInterface
    {
        if ($request->errors !== []) {
            return $this->response
                ->setStatusCode(422)
                ->setBody(view('posts/new', [
                    'form'   => $request->form,
                    'errors' => $request->errors,
                ]));
        }

        $data = $request->getValidated();

        // Save the post...

        return redirect()->to('/posts');
    }
}
