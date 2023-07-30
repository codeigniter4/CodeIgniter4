<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\Live\SQLite;

use CodeIgniter\Database\Exceptions\DataException;
use CodeIgniter\Database\SQLite3\Forge;
use CodeIgniter\Database\SQLite3\Table;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;

/**
 * @group DatabaseLive
 *
 * @requires extension sqlite3
 *
 * @internal
 */
final class AlterTableTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    /**
     * In setUp() db connection is changed. So migration doesn't work
     *
     * @var bool
     */
    protected $migrate = false;

    private Table $table;
    private Forge $forge;

    protected function setUp(): void
    {
        parent::setUp();

        $config = [
            'DBDriver' => 'SQLite3',
            'database' => ':memory:',
            'DBDebug'  => true,
        ];

        $this->db    = db_connect($config);
        $this->forge = Database::forge($config);
        $this->table = new Table($this->db, $this->forge);

        $this->dropTables();
    }

    private function dropTables(): void
    {
        $this->forge->dropTable('aliens', true);
        $this->forge->dropTable('aliens_fk', true);
        $this->forge->dropTable('janky', true);
        $this->forge->dropTable('janky_fk', true);
        $this->forge->dropTable('foo', true);
        $this->forge->dropTable('foo_fk', true);
    }

    public function testFromTableThrowsOnNoTable(): void
    {
        $this->expectException(DataException::class);
        $this->expectExceptionMessage('Table "foo" was not found in the current database.');

        $this->table->fromTable('foo');
    }

    public function testFromTableFillsDetails(): void
    {
        $this->createTable();

        $this->assertTrue($this->db->tableExists('foo'));

        $this->table->fromTable('foo');

        $fields = $this->getPrivateProperty($this->table, 'fields');

        $this->assertCount(5, $fields);
        $this->assertArrayHasKey('id', $fields);
        $this->assertNull($fields['id']['default']);
        $this->assertTrue($fields['id']['null']);
        $this->assertSame('integer', strtolower($fields['id']['type']));

        $this->assertArrayHasKey('name', $fields);
        $this->assertNull($fields['name']['default']);
        $this->assertFalse($fields['name']['null']);
        $this->assertSame('varchar', strtolower($fields['name']['type']));

        $this->assertArrayHasKey('email', $fields);
        $this->assertNull($fields['email']['default']);
        $this->assertTrue($fields['email']['null']);
        $this->assertSame('varchar', strtolower($fields['email']['type']));

        $keys = $this->getPrivateProperty($this->table, 'keys');

        $this->assertCount(3, $keys);
        $this->assertArrayHasKey('foo_name', $keys);
        $this->assertSame(['fields' => ['name'], 'type' => 'index'], $keys['foo_name']);
        $this->assertArrayHasKey('foo_email', $keys);
        $this->assertSame(['fields' => ['email'], 'type' => 'unique'], $keys['foo_email']);
        $this->assertArrayHasKey('primary', $keys);
        $this->assertSame(['fields' => ['id'], 'type' => 'primary'], $keys['primary']);
    }

    public function testDropColumnSuccess(): void
    {
        $this->createTable();

        $result = $this->table
            ->fromTable('foo')
            ->dropColumn('name')
            ->run();

        $this->assertTrue($result);

        $columns = $this->db->getFieldNames('foo');

        $this->assertNotContains('name', $columns);
        $this->assertContains('id', $columns);
        $this->assertContains('email', $columns);
    }

    public function testDropColumnMaintainsKeys(): void
    {
        $this->createTable();

        $oldKeys = $this->db->getIndexData('foo');

        $this->assertArrayHasKey('foo_name', $oldKeys);
        $this->assertArrayHasKey('foo_email', $oldKeys);

        $result = $this->table
            ->fromTable('foo')
            ->dropColumn('name')
            ->run();

        $newKeys = $this->db->getIndexData('foo');

        $this->assertArrayNotHasKey('foo_name', $newKeys);
        $this->assertArrayHasKey('foo_email', $newKeys);

        $this->assertTrue($result);
    }

    public function testDropColumnDropCompositeKey(): void
    {
        $this->forge->dropTable('actions', true);

        $fields = [
            'category'   => ['type' => 'varchar', 'constraint' => 63],
            'name'       => ['type' => 'varchar', 'constraint' => 63],
            'created_at' => ['type' => 'datetime', 'null' => true],
        ];

        $this->forge->addField('id');
        $this->forge->addField($fields);

        $this->forge->addKey('name');
        $this->forge->addKey(['category', 'name']);
        $this->forge->addKey('created_at');

        $this->forge->createTable('actions');

        $indexes = $this->db->getIndexData('actions');

        // the composite index was created
        $this->assertSame(['category', 'name'], $indexes['actions_category_name']->fields);

        // drop one of the columns in the composite index
        $this->forge->dropColumn('actions', 'category');

        // get indexes again
        $indexes = $this->db->getIndexData('actions');

        // check that composite index was dropped.
        $this->assertArrayNotHasKey('actions_category_name', $indexes);

        // check that that other keys are present
        $this->assertArrayHasKey('actions_name', $indexes);
        $this->assertArrayHasKey('actions_created_at', $indexes);

        $this->forge->dropTable('actions');
    }

    public function testModifyColumnSuccess(): void
    {
        $this->createTable('janky');

        $result = $this->table
            ->fromTable('janky')
            ->modifyColumn([
                [
                    'name'       => 'name',
                    'new_name'   => 'serial',
                    'type'       => 'int',
                    'constraint' => 11,
                    'null'       => true,
                ],
            ])
            ->run();

        $this->assertTrue($result);

        $this->assertFalse($this->db->fieldExists('name', 'janky'));
        $this->assertTrue($this->db->fieldExists('serial', 'janky'));
    }

    public function testDropForeignKeySuccess(): void
    {
        $this->createTable('aliens');

        $keys = $this->db->getForeignKeyData('aliens');
        $this->assertSame($this->db->DBPrefix . 'aliens_key_id_foreign', $keys[$this->db->DBPrefix . 'aliens_key_id_foreign']->constraint_name);

        $result = $this->table
            ->fromTable('aliens')
            ->dropForeignKey('aliens_key_id_foreign')
            ->run();

        $this->assertTrue($result);

        $keys = $this->db->getForeignKeyData('aliens');
        $this->assertEmpty($keys);
    }

    public function testProcessCopiesOldData(): void
    {
        $this->createTable();

        $this->db->table('foo_fk')->insert([
            'id'   => 1,
            'name' => 'bar',
        ]);

        $this->db->table('foo')->insert([
            'id'     => 1,
            'name'   => 'George Clinton',
            'email'  => 'funkalicious@example.com',
            'key_id' => 1,
        ]);

        $this->seeInDatabase('foo', ['name' => 'George Clinton']);

        $this->table
            ->fromTable('foo')
            ->dropColumn('name')
            ->run();

        $this->assertFalse($this->db->fieldExists('name', 'foo'));
        $this->seeInDatabase('foo', ['email' => 'funkalicious@example.com']);
    }

    protected function createTable(string $tableName = 'foo'): void
    {
        // Create support table for foreign keys
        $this->forge->addField([
            'id' => [
                'type'           => 'integer',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'varchar',
                'constraint' => 255,
                'null'       => false,
            ],
        ]);
        $this->forge->createTable($tableName . '_fk');

        // Create main table
        $this->forge->addField([
            'id' => [
                'type'           => 'integer',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'varchar',
                'constraint' => 255,
                'null'       => false,
            ],
            'email' => [
                'type'       => 'varchar',
                'constraint' => 255,
                'null'       => true,
            ],
            'key_id' => [
                'type'       => 'integer',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'group' => [
                'type'       => 'varchar',
                'constraint' => 255,
                'null'       => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('name');
        $this->forge->addUniqueKey('email');
        $this->forge->addForeignKey('key_id', $tableName . '_fk', 'id');
        $this->forge->createTable($tableName);
    }
}
