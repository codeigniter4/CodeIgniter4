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

    public function testGetForeignKeyDataWithParenthesesInTableName(): void
    {
        $this->db->query('DROP TABLE IF EXISTS "parent(v1)"');
        $this->db->query('DROP TABLE IF EXISTS "child(v2)"');

        $this->db->query('CREATE TABLE "parent(v1)" (id INTEGER PRIMARY KEY)');
        $this->db->query('CREATE TABLE "child(v2)" (id INTEGER PRIMARY KEY, parent_id INTEGER, FOREIGN KEY (parent_id) REFERENCES "parent(v1)"(id))');

        $foreignKeys = $this->db->getForeignKeyData('child(v2)');

        $this->assertCount(1, $foreignKeys);
        $firstKey = reset($foreignKeys);
        $this->assertSame('child(v2)', $firstKey->table_name);
        $this->assertSame(['parent_id'], $firstKey->column_name);
        $this->assertSame('parent(v1)', $firstKey->foreign_table_name);
        $this->assertSame(['id'], $firstKey->foreign_column_name);

        $this->db->query('DROP TABLE IF EXISTS "child(v2)"');
        $this->db->query('DROP TABLE IF EXISTS "parent(v1)"');
    }

    public function testGetForeignKeyDataWithQuotesAndSpacesInTableName(): void
    {
        $this->db->query('DROP TABLE IF EXISTS "parent table"');
        $this->db->query('DROP TABLE IF EXISTS "child\'quote with space"');

        $this->db->query('CREATE TABLE "parent table" (id INTEGER PRIMARY KEY)');
        $this->db->query('CREATE TABLE "child\'quote with space" (id INTEGER PRIMARY KEY, p_id INTEGER, FOREIGN KEY (p_id) REFERENCES "parent table"(id))');

        $foreignKeys = $this->db->getForeignKeyData("child'quote with space");

        $this->assertCount(1, $foreignKeys);
        $firstKey = reset($foreignKeys);
        $this->assertSame("child'quote with space", $firstKey->table_name);
        $this->assertSame(['p_id'], $firstKey->column_name);
        $this->assertSame('parent table', $firstKey->foreign_table_name);
        $this->assertSame(['id'], $firstKey->foreign_column_name);

        $this->db->query('DROP TABLE IF EXISTS "child\'quote with space"');
        $this->db->query('DROP TABLE IF EXISTS "parent table"');
    }

    public function testGetForeignKeyDataWithNumericAndReservedWordTableName(): void
    {
        $this->db->query('DROP TABLE IF EXISTS "order"');
        $this->db->query('DROP TABLE IF EXISTS "12345"');

        $this->db->query('CREATE TABLE "order" (id INTEGER PRIMARY KEY)');
        $this->db->query('CREATE TABLE "12345" (id INTEGER PRIMARY KEY, order_id INTEGER, FOREIGN KEY (order_id) REFERENCES "order"(id))');

        $foreignKeys = $this->db->getForeignKeyData('12345');

        $this->assertCount(1, $foreignKeys);
        $firstKey = reset($foreignKeys);
        $this->assertSame('12345', $firstKey->table_name);
        $this->assertSame(['order_id'], $firstKey->column_name);
        $this->assertSame('order', $firstKey->foreign_table_name);
        $this->assertSame(['id'], $firstKey->foreign_column_name);

        $this->db->query('DROP TABLE IF EXISTS "12345"');
        $this->db->query('DROP TABLE IF EXISTS "order"');
    }

    public function testGetForeignKeyDataWithAttachedDatabaseSpecialIdentifiers(): void
    {
        $this->db->query('ATTACH DATABASE ":memory:" AS "schema-db(1)"');
        $this->db->query('DROP TABLE IF EXISTS "schema-db(1)"."parent(v1)"');
        $this->db->query('DROP TABLE IF EXISTS "schema-db(1)"."child(v2)"');

        $this->db->query('CREATE TABLE "schema-db(1)"."parent(v1)" (id INTEGER PRIMARY KEY)');
        $this->db->query('CREATE TABLE "schema-db(1)"."child(v2)" (id INTEGER PRIMARY KEY, p_id INTEGER, FOREIGN KEY (p_id) REFERENCES "parent(v1)"(id))');

        $foreignKeys = $this->db->getForeignKeyData('schema-db(1).child(v2)');

        $this->assertCount(1, $foreignKeys);
        $firstKey = reset($foreignKeys);
        $this->assertSame('schema-db(1).child(v2)', $firstKey->table_name);
        $this->assertSame(['p_id'], $firstKey->column_name);
        $this->assertSame('parent(v1)', $firstKey->foreign_table_name);
        $this->assertSame(['id'], $firstKey->foreign_column_name);

        $this->db->query('DROP TABLE IF EXISTS "schema-db(1)"."child(v2)"');
        $this->db->query('DROP TABLE IF EXISTS "schema-db(1)"."parent(v1)"');
        $this->db->query('DETACH DATABASE "schema-db(1)"');
    }
}
