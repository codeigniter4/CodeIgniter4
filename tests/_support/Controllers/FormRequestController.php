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
        return json_encode(['id' => $id, 'format' => $format, 'data' => $request->validated()]);
    }

    /**
     * Receives only a FormRequest (no route params).
     */
    public function store(ValidPostFormRequest $request): string
    {
        return json_encode($request->validated());
    }

    /**
     * Receives a route param alongside a FormRequest.
     */
    public function update(string $id, ValidPostFormRequest $request): string
    {
        return json_encode(['id' => $id, 'data' => $request->validated()]);
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
        return json_encode(['tags' => $tags, 'data' => $request->validated()]);
    }

    /**
     * Uses an always-unauthorized FormRequest.
     */
    public function restricted(UnauthorizedFormRequest $request): string
    {
        return 'should-not-reach';
    }
}
