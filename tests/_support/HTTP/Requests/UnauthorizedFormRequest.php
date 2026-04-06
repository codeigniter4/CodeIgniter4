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

/**
 * A FormRequest that always denies authorization.
 */
class UnauthorizedFormRequest extends FormRequest
{
    public function rules(): array
    {
        return ['title' => 'required'];
    }

    public function isAuthorized(): bool
    {
        return false;
    }
}
