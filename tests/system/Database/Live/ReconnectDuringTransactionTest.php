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

namespace CodeIgniter\Database\Live;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class ReconnectDuringTransactionTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = false;

    protected function setUp(): void
    {
        // Dedicated connection so a forced wait_timeout cannot drop the shared one.
        $this->db = Database::connect($this->DBGroup, false);

        parent::setUp();
    }

    public function testReconnectInsideTransactionDoesNotReportSuccess(): void
    {
        if ($this->db->DBDriver !== 'MySQLi') {
            $this->markTestSkipped('Requires the MySQLi driver.');
        }

        $table = $this->db->escapeIdentifier($this->db->prefixTable('reconnect_probe'));
        $this->db->query('DROP TABLE IF EXISTS ' . $table);
        $this->db->query(
            'CREATE TABLE ' . $table . ' ('
            . 'id INT NOT NULL AUTO_INCREMENT, '
            . 'note VARCHAR(32) NOT NULL, '
            . 'PRIMARY KEY (id)'
            . ') ENGINE=InnoDB',
        );

        $observer = Database::connect($this->DBGroup, false);
        $observer->initialize();

        try {
            $this->db->transStart();
            $this->db->table('reconnect_probe')->insert(['note' => 'before-timeout']);
            $this->assertSame(1, $this->db->transDepth);
            $this->assertTrue($this->db->transStatus());

            $this->db->query('SET SESSION wait_timeout = 1');
            sleep(3);

            $this->assertFalse($this->db->ping(), 'Precondition: the server should have closed the idle socket.');

            $this->db->reconnect();

            $depthAfterReconnect  = $this->db->transDepth;
            $statusAfterReconnect = $this->db->transStatus();
            $completed            = $this->db->transComplete();
            $rows                 = (int) $observer->table('reconnect_probe')->countAllResults();

            $this->assertSame(
                ['depth' => 0, 'status' => false, 'completed' => false, 'rows' => 0],
                [
                    'depth'     => $depthAfterReconnect,
                    'status'    => $statusAfterReconnect,
                    'completed' => $completed,
                    'rows'      => $rows,
                ],
            );
        } finally {
            if ($this->db->transDepth > 0) {
                $this->db->transRollback();
            }

            $this->db->query('DROP TABLE IF EXISTS ' . $table);
            $observer->close();
            $this->db->close();
        }
    }

    public function testReconnectKeepsAnOpenTransactionWhenTheSocketIsAlive(): void
    {
        if ($this->db->DBDriver !== 'MySQLi') {
            $this->markTestSkipped('Requires the MySQLi driver.');
        }

        $table = $this->db->escapeIdentifier($this->db->prefixTable('reconnect_probe'));
        $this->db->query('DROP TABLE IF EXISTS ' . $table);
        $this->db->query(
            'CREATE TABLE ' . $table . ' ('
            . 'id INT NOT NULL AUTO_INCREMENT, '
            . 'note VARCHAR(32) NOT NULL, '
            . 'PRIMARY KEY (id)'
            . ') ENGINE=InnoDB',
        );

        try {
            $this->db->transStart();
            $this->db->table('reconnect_probe')->insert(['note' => 'still-open']);
            $this->assertTrue($this->db->ping());

            $this->db->reconnect();

            $this->assertSame(1, $this->db->transDepth);
            $this->assertTrue($this->db->transStatus());
            $this->assertTrue($this->db->transComplete());
            $this->assertSame(1, $this->db->table('reconnect_probe')->countAllResults());
        } finally {
            if ($this->db->transDepth > 0) {
                $this->db->transRollback();
            }

            $this->db->query('DROP TABLE IF EXISTS ' . $table);
            $this->db->close();
        }
    }
}
