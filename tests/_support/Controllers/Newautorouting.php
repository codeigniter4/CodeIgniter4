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

class Newautorouting extends Controller
{
    public function getIndex(string $m = '')
    {
        return 'Hello';
    }

    public function postSave(int $a, string $b, $c = null)
    {
        return 'Saved';
    }
}
