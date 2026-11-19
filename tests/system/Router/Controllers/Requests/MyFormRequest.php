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

namespace CodeIgniter\Router\Controllers\Requests;

use CodeIgniter\HTTP\FormRequest;
use Override;

class MyFormRequest extends FormRequest
{
    #[Override]
    public function rules(): array
    {
        return [];
    }
}
