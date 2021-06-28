<?php

namespace CodeIgniter\Database\Live\SQLite;

use CodeIgniter\Database\SQLite3\Connection;
use CodeIgniter\Database\SQLite3\Forge;
use CodeIgniter\Database\SQLite3\Table;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;

/**
 * @group DatabaseLive
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

    /**
     * @var Table
     */
    protected $table;

    /**
     * @var Connection
     */
    protected $db;

    /**
     * @var Forge
     */
    protected $forge;

    protected function setUp(): void
    {
        parent::setUp();

        $config = [
            'DBDriver' => 'SQLite3',
            'database' => 'database.db',
        ];

        $this->db    = db_connect($config);
        $this->forge = Database::forge($config);
        $this->table = new Table($this->db, $this->forge);

        $this->dropTables();
    }

    private function dropTables()
    {
        $this->forge->dropTable('aliens', true);
        $this->forge->dropTable('aliens_fk', true);
        $this->forge->dropTable('janky', true);
        $this->forge->dropTable('janky_fk', true);
        $this->forge->dropTable('foo', true);
        $this->forge->dropTable('foo_fk', true);
    }

    public function testFromTableThrowsOnNoTable()
    {
        $this->expectException('CodeIgniter\Database\Exceptions\DataException');
        $this->expectExceptionMessage('Table `foo` was not found in the current database.');

        $this->table->fromTable('foo');
    }

    public function testFromTableFillsDetails()
    {
        $this->createTable();

        $this->assertTrue($this->db->tableExists('foo'));

        $this->table->fromTable('foo');

        $fields = $this->getPrivateProperty($this->table, 'fields');

        $this->assertCount(4, $fields);
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
        $this->assertArrayHasKey('id', $keys);
        $this->assertSame(['fields' => ['id'], 'type' => 'primary'], $keys['id']);
        $this->assertArrayHasKey('id', $keys);
        $this->assertSame(['fields' => ['id'], 'type' => 'primary'], $keys['id']);
    }

    public function testDropColumnSuccess()
    {
        $this->createTable();

        $result = $this->table
            ->fromTable('foo')
            ->dropColumn('name')
            ->run();

        $this->assertTrue($result);

        $columns = $this->db->getFieldNames('foo');

        $this->assertFalse(in_array('name', $columns, true));
        $this->assertTrue(in_array('id', $columns, true));
        $this->assertTrue(in_array('email', $columns, true));
    }

    public function testDropColumnMaintainsKeys()
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

    public function testModifyColumnSuccess()
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

    public function testDropForeignKeySuccess()
    {
        $this->createTable('aliens');

        $keys = $this->db->getForeignKeyData('aliens');
        $this->assertSame('key_id to aliens_fk.id', $keys[0]->constraint_name);

        $result = $this->table
            ->fromTable('aliens')
            ->dropForeignKey('key_id')
            ->run();

        $this->assertTrue($result);

        $keys = $this->db->getForeignKeyData('aliens');
        $this->assertEmpty($keys);
    }

    public function testProcessCopiesOldData()
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

        $this->dontSeeInDatabase('foo', ['name' => 'George Clinton']);
        $this->seeInDatabase('foo', ['email' => 'funkalicious@example.com']);
    }

    protected function createTable(string $tableName = 'foo')
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
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('name');
        $this->forge->addUniqueKey('email');
        $this->forge->addForeignKey('key_id', $tableName . '_fk', 'id');
        $this->forge->createTable($tableName);
    }
}
