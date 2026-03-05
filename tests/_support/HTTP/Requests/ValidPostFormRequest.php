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
 * A FormRequest used in integration tests requiring title + body.
 *
 * @property-read string $body
 * @property-read string $title
 */
class ValidPostFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|min_length[3]',
            'body'  => 'required',
        ];
    }
}
