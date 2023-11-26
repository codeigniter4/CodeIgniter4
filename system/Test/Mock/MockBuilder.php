<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Test\Mock;

use CodeIgniter\Database\BaseBuilder;

class MockBuilder extends BaseBuilder
{
    protected $supportedIgnoreStatements = [
        'update' => 'IGNORE',
        'insert' => 'IGNORE',
        'delete' => 'IGNORE',
    ];
}
