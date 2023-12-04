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

namespace CodeIgniter\Database;

use Config\Database;

/**
 * Class Migration
 */
abstract class Migration
{
    /**
     * The name of the database group to use.
     *
     * @var string
     */
    protected $DBGroup;

    /**
     * Database Connection instance
     *
     * @var ConnectionInterface
     */
    protected $db;

    /**
     * Database Forge instance.
     *
     * @var Forge
     */
    protected $forge;

    /**
     * Constructor.
     */
    public function __construct(?Forge $forge = null)
    {
        $this->forge = $forge ?? Database::forge($this->DBGroup ?? config(Database::class)->defaultGroup);

        $this->db = $this->forge->getConnection();
    }

    /**
     * Returns the database group name this migration uses.
     */
    public function getDBGroup(): ?string
    {
        return $this->DBGroup;
    }

    /**
     * Perform a migration step.
     */
    abstract public function up();

    /**
     * Revert a migration step.
     */
    abstract public function down();
}
