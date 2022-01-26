<?php

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

class Remap extends Controller
{
    public function _remap($method, ...$params)
    {
        if ($method === 'xyz') {
            return $this->abc();
        }

        return $this->index();
    }

    public function index()
    {
        return 'index';
    }

    public function abc()
    {
        return 'abc';
    }
}
