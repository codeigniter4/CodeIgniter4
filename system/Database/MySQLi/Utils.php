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

namespace CodeIgniter\Database\MySQLi;

use CodeIgniter\Database\BaseUtils;
use CodeIgniter\Database\Exceptions\DatabaseException;

/**
 * Utils for MySQLi
 *
 * @extends BaseUtils<Connection>
 */
class Utils extends BaseUtils
{
    /**
     * @var bool|string
     */
    protected $listDatabases = 'SHOW DATABASES';

    /**
     * @var bool|string
     */
    protected $optimizeTable = 'OPTIMIZE TABLE %s';

    /**
     * @return never
     */
    public function _backup(?array $prefs = null)
    {
        throw new DatabaseException('Unsupported feature of the database platform you are using.');
    }
}
