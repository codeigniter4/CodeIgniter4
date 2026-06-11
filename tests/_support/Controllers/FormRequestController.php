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

namespace Tests\Support\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;
use Tests\Support\HTTP\Requests\ContinuingPostFormRequest;
use Tests\Support\HTTP\Requests\UnauthorizedFormRequest;
use Tests\Support\HTTP\Requests\ValidPostFormRequest;

/**
 * Controller used in FormRequest integration tests.
 */
class FormRequestController extends Controller
{
    /**
     * Optional trailing param after a FormRequest - verifies that the optional
     * param gets its default value when the corresponding URI segment is absent.
     */
    public function index(string $id, ValidPostFormRequest $request, string $format = 'json'): string
    {
        return json_encode(['id' => $id, 'format' => $format, 'data' => $request->getValidated()]);
    }

    /**
     * Receives only a FormRequest (no route params).
     */
    public function store(ValidPostFormRequest $request): string
    {
        return json_encode($request->getValidated());
    }

    /**
     * Handles validation failures in the controller.
     */
    public function storeContinuing(ContinuingPostFormRequest $request): ResponseInterface
    {
        if ($request->errors !== []) {
            return $this->response->setStatusCode(422)->setJSON([
                'errors'    => $request->errors,
                'form'      => $request->form,
                'validated' => $request->getValidated(),
            ]);
        }

        return $this->response->setJSON(['validated' => $request->getValidated()]);
    }

    /**
     * Receives a route param alongside a FormRequest.
     */
    public function update(string $id, ValidPostFormRequest $request): string
    {
        return json_encode(['id' => $id, 'data' => $request->getValidated()]);
    }

    /**
     * No FormRequest - verifies BC with plain route params.
     */
    public function show(string $id): string
    {
        return 'item-' . $id;
    }

    /**
     * Variadic route params alongside a FormRequest - verifies that all extra
     * URI segments are collected into the variadic array.
     */
    public function search(ValidPostFormRequest $request, string ...$tags): string
    {
        return json_encode(['tags' => $tags, 'data' => $request->getValidated()]);
    }

    /**
     * Uses an always-unauthorized FormRequest.
     */
    public function restricted(UnauthorizedFormRequest $request): string
    {
        return 'should-not-reach';
    }
}
