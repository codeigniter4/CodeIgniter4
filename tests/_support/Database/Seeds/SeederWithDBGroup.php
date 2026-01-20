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

namespace Tests\Support\Database\Seeds;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Seeder;

/**
 * Test seeder with explicit DBGroup set.
 */
class SeederWithDBGroup extends Seeder
{
    protected $DBGroup = 'tests';

    /**
     * Store the connection used during run() for testing.
     */
    public static ?BaseConnection $lastConnection = null;

    public function run(): void
    {
        self::$lastConnection = $this->db;
    }

    /**
     * Expose the db connection for testing.
     */
    public function getDatabase(): BaseConnection
    {
        return $this->db;
    }

    /**
     * Reset static state for testing.
     */
    public static function reset(): void
    {
        self::$lastConnection = null;
    }
}
