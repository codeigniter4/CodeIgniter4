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

namespace CodeIgniter\Database\Live\SQLite3;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class GetForeignKeyDataTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->db = Database::connect($this->DBGroup);
        if ($this->db->DBDriver !== 'SQLite3') {
            $this->markTestSkipped('This test is only for SQLite3.');
        }

        $config = [
            'DBDriver' => 'SQLite3',
            'database' => 'database.db',
            'DBDebug'  => true,
        ];
        $this->db = db_connect($config, false);
    }

    public function testGetForeignKeyDataWithSpecialTableName(): void
    {
        $this->db->query('DROP TABLE IF EXISTS `test-parent`');
        $this->db->query('DROP TABLE IF EXISTS `test-child`');

        $this->db->query('CREATE TABLE `test-parent` (id INTEGER PRIMARY KEY)');
        $this->db->query('CREATE TABLE `test-child` (id INTEGER PRIMARY KEY, parent_id INTEGER, FOREIGN KEY (parent_id) REFERENCES `test-parent`(id))');

        $foreignKeys = $this->db->getForeignKeyData('test-child');

        $this->assertCount(1, $foreignKeys);
        $firstKey = reset($foreignKeys);
        $this->assertSame('test-child', $firstKey->table_name);
        $this->assertSame(['parent_id'], $firstKey->column_name);
        $this->assertSame('test-parent', $firstKey->foreign_table_name);
        $this->assertSame(['id'], $firstKey->foreign_column_name);

        $this->db->query('DROP TABLE IF EXISTS `test-child`');
        $this->db->query('DROP TABLE IF EXISTS `test-parent`');
    }

    public function testGetForeignKeyDataWithAttachedDatabase(): void
    {
        $this->db->query('ATTACH DATABASE ":memory:" AS `test_schema`');
        $this->db->query('DROP TABLE IF EXISTS `test_schema`.`attached-parent`');
        $this->db->query('DROP TABLE IF EXISTS `test_schema`.`attached-child`');

        $this->db->query('CREATE TABLE `test_schema`.`attached-parent` (id INTEGER PRIMARY KEY)');
        $this->db->query('CREATE TABLE `test_schema`.`attached-child` (id INTEGER PRIMARY KEY, p_id INTEGER, FOREIGN KEY (p_id) REFERENCES `attached-parent`(id))');

        $foreignKeys = $this->db->getForeignKeyData('test_schema.attached-child');

        $this->assertCount(1, $foreignKeys);
        $firstKey = reset($foreignKeys);
        $this->assertSame('test_schema.attached-child', $firstKey->table_name);
        $this->assertSame(['p_id'], $firstKey->column_name);
        $this->assertSame('attached-parent', $firstKey->foreign_table_name);
        $this->assertSame(['id'], $firstKey->foreign_column_name);

        $this->db->query('DROP TABLE IF EXISTS `test_schema`.`attached-child`');
        $this->db->query('DROP TABLE IF EXISTS `test_schema`.`attached-parent`');
        $this->db->query('DETACH DATABASE `test_schema`');
    }
}
