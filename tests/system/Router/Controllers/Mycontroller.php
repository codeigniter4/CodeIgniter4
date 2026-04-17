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

namespace CodeIgniter\Router\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\Router\Controllers\Requests\MyFormRequest;

class Mycontroller extends Controller
{
    public function getIndex(): void
    {
    }

    public function getSomemethod($first = ''): void
    {
    }

    public function getFormmethod(MyFormRequest $request): void
    {
    }

    public function getFormmethodWithParam(string $id, MyFormRequest $request): void
    {
    }

    public function getFormmethodVariadic(MyFormRequest $request, string ...$tags): void
    {
    }
}
