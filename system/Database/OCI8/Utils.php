<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\OCI8;

use CodeIgniter\Database\BaseUtils;
use CodeIgniter\Database\Exceptions\DatabaseException;

/**
 * Utils for OCI8
 */
class Utils extends BaseUtils
{
    /**
     * List databases statement
     *
     * @var string
     */
    protected $listDatabases = 'SELECT TABLESPACE_NAME FROM USER_TABLESPACES';

    /**
     * Platform dependent version of the backup function.
     *
     * @return never
     */
    public function _backup(?array $prefs = null)
    {
        throw new DatabaseException('Unsupported feature of the database platform you are using.');
    }
}
