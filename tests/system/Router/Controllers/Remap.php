<?php

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
use CodeIgniter\Exceptions\PageNotFoundException;

class Remap extends Controller
{
    public function _remap(string $method, ...$params): string
    {
        $method = 'process_' . $method;

        if (method_exists($this, $method)) {
            return $this->{$method}(...$params);
        }

        throw PageNotFoundException::forPageNotFound();
    }

    public function getTest(): string
    {
        return __METHOD__;
    }

    protected function process_index(): string
    {
        return __METHOD__;
    }
}
