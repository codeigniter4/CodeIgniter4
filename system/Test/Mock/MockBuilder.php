<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Test\Mock;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\ConnectionInterface;

class MockBuilder extends BaseBuilder
{
    public function __construct($tableName, ConnectionInterface &$db, ?array $options = null)
    {
        parent::__construct($tableName, $db, $options);
    }
}
