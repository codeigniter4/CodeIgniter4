<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\Support\HTTP\Requests;

use CodeIgniter\HTTP\FormRequest;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * A FormRequest that lets validation failures reach the controller.
 */
class ContinuingPostFormRequest extends FormRequest
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

    protected function prepareForValidation(array $data): array
    {
        if (isset($data['title'])) {
            $data['title'] = trim((string) $data['title']);
        }

        return $data;
    }

    protected function failedValidation(array $errors, array $preparedData): ?ResponseInterface
    {
        $this->errors = $errors;
        $this->form   = $preparedData;

        return null;
    }
}
