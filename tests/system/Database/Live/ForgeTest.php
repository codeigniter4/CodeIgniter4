<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\Live;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Database\Forge;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;
use LogicException;
use RuntimeException;
use stdClass;
use Tests\Support\Database\Seeds\CITestSeeder;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class ForgeTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $seed    = CITestSeeder::class;
    private Forge $forge;

    protected function setUp(): void
    {
        $this->forge = Database::forge($this->DBGroup);

        // when running locally if one of these tables isn't dropped it may cause error
        $this->forge->dropTable('forge_test_invoices', true);
        $this->forge->dropTable('forge_test_inv', true);
        $this->forge->dropTable('forge_test_users', true);
        $this->forge->dropTable('actions', true);

        parent::setUp();
    }

    public function testCreateDatabase(): void
    {
        if ($this->db->DBDriver === 'OCI8') {
            $this->markTestSkipped('OCI8 does not support create database.');
        }
        $databaseCreated = $this->forge->createDatabase('test_forge_database');

        $this->assertTrue($databaseCreated);
    }

    public function testCreateDatabaseIfNotExists(): void
    {
        if ($this->db->DBDriver === 'OCI8') {
            $this->markTestSkipped('OCI8 does not support create database.');
        }
        $dbName = 'test_forge_database_exist';

        $databaseCreateIfNotExists = $this->forge->createDatabase($dbName, true);
        if ($this->db->DBDriver !== 'SQLite3') {
            $this->forge->dropDatabase($dbName);
        }

        $this->assertTrue($databaseCreateIfNotExists);
    }

    public function testCreateDatabaseIfNotExistsWithDb(): void
    {
        if ($this->db->DBDriver === 'OCI8') {
            $this->markTestSkipped('OCI8 does not support create database.');
        }
        $dbName = 'test_forge_database_exist';

        $this->forge->createDatabase($dbName);
        $databaseExists = $this->forge->createDatabase($dbName, true);
        if ($this->db->DBDriver !== 'SQLite3') {
            $this->forge->dropDatabase($dbName);
        }

        $this->assertTrue($databaseExists);
    }

    public function testDropDatabase(): void
    {
        if ($this->db->DBDriver === 'OCI8') {
            $this->markTestSkipped('OCI8 does not support drop database.');
        }
        if ($this->db->DBDriver === 'SQLite3') {
            $this->markTestSkipped('SQLite3 requires file path to drop database');
        }

        $databaseDropped = $this->forge->dropDatabase('test_forge_database');

        $this->assertTrue($databaseDropped);
    }

    public function testCreateDatabaseExceptionNoCreateStatement(): void
    {
        $this->setPrivateProperty($this->forge, 'createDatabaseStr', false);

        if ($this->db->DBDriver === 'SQLite3') {
            $databaseCreated = $this->forge->createDatabase('test_forge_database');
            $this->assertTrue($databaseCreated);
        } else {
            $this->expectException(DatabaseException::class);
            $this->expectExceptionMessage('This feature is not available for the database you are using.');

            $this->forge->createDatabase('test_forge_database');
        }
    }

    public function testDropDatabaseExceptionNoDropStatement(): void
    {
        $this->setPrivateProperty($this->forge, 'dropDatabaseStr', false);

        if ($this->db->DBDriver === 'SQLite3') {
            $this->markTestSkipped('SQLite3 requires file path to drop database');
        } else {
            $this->expectException(DatabaseException::class);
            $this->expectExceptionMessage('This feature is not available for the database you are using.');

            $this->forge->dropDatabase('test_forge_database');
        }
    }

    public function testCreateTable(): void
    {
        $this->forge->dropTable('forge_test_table', true);

        $this->forge->addField([
            'id' => [
                'type'           => 'INTEGER',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'mobile' => [
                'type'       => 'INTEGER',
                'constraint' => 10,
                'unsigned'   => true,
            ],
        ]);

        $unsignedAttributes = [
            'INTEGER',
        ];

        $this->setPrivateProperty($this->forge, 'unsigned', $unsignedAttributes);

        $this->forge->addKey('id', true);
        $this->forge->createTable('forge_test_table');

        $exist = $this->db->tableExists('forge_test_table');

        $this->assertTrue($exist);
        $this->forge->dropTable('forge_test_table', true);
    }

    public function testCreateTableWithExists(): void
    {
        // create table so that it exists in database
        $this->forge->addField([
            'id'   => ['type' => 'INTEGER', 'constraint' => 3, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 80],
        ])->addKey('id', true)->createTable('test_exists', true);

        // table exists in cache
        $this->assertTrue($this->db->tableExists('db_test_exists', true));

        // table exists without cached results
        $this->assertTrue($this->db->tableExists('db_test_exists', false));

        // try creating table when table exists
        $result = $this->forge->addField([
            'id'   => ['type' => 'INTEGER', 'constraint' => 3, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 80],
        ])->addKey('id', true)->createTable('test_exists', true);

        $this->assertTrue($result);

        // Delete table outside of forge. This should leave table in cache as existing.
        $this->db->query('DROP TABLE ' . $this->db->protectIdentifiers('db_test_exists', true, null, false));

        // table stil exists in cache
        $this->assertTrue($this->db->tableExists('db_test_exists', true));

        // table does not exist without cached results - this will update the cache
        $this->assertFalse($this->db->tableExists('db_test_exists', false));

        // the call above should update the cache - table should not exist in cache anymore
        $this->assertFalse($this->db->tableExists('db_test_exists', true));

        // try creating table when table does not exist but still in cache
        $result = $this->forge->addField([
            'id'   => ['type' => 'INTEGER', 'constraint' => 3, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 80],
        ])->addKey('id', true)->createTable('test_exists', true);

        $this->assertTrue($result);

        // check that the table does now exist without cached results
        $this->assertTrue($this->db->tableExists('db_test_exists', false));

        // drop table so that it doesn't mess up other tests
        $this->forge->dropTable('test_exists');
    }

    public function testCreateTableApplyBigInt(): void
    {
        $this->forge->dropTable('forge_test_table', true);

        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('forge_test_table', true);

        $fieldsData = $this->db->getFieldData('forge_test_table');
        if ($this->db->DBDriver === 'MySQLi') {
            $this->assertSame(strtolower($fieldsData[0]->type), 'bigint');
        } elseif ($this->db->DBDriver === 'Postgre') {
            $this->assertSame(strtolower($fieldsData[0]->type), 'bigint');
        } elseif ($this->db->DBDriver === 'SQLite3') {
            $this->assertSame(strtolower($fieldsData[0]->type), 'integer');
        } elseif ($this->db->DBDriver === 'OCI8') {
            $this->assertSame(strtolower($fieldsData[0]->type), 'number');
        } elseif ($this->db->DBDriver === 'SQLSRV') {
            $this->assertSame(strtolower($fieldsData[0]->type), 'bigint');
        }

        $this->forge->dropTable('forge_test_table', true);
    }

    public function testCreateTableWithAttributes(): void
    {
        if ($this->db->DBDriver === 'OCI8') {
            $this->markTestSkipped('OCI8 does not support comments on tables or columns.');
        }
        if ($this->db->DBDriver === 'SQLite3') {
            $this->markTestSkipped('SQLite3 does not support comments on tables or columns.');
        }

        $this->forge->dropTable('forge_test_attributes', true);

        $this->forge->addField('id');

        $attributes = [
            'comment' => "Forge's Test",
        ];

        $this->forge->createTable('forge_test_attributes', false, $attributes);

        $exist = $this->db->tableExists('forge_test_attributes');
        $this->forge->dropTable('forge_test_attributes', true, true);

        $this->assertTrue($exist);
    }

    public function testCreateTableWithArrayFieldConstraints(): void
    {
        if (in_array($this->db->DBDriver, ['MySQLi', 'SQLite3'], true)) {
            $this->forge->dropTable('forge_array_constraint', true);
            $this->forge->addField([
                'status' => [
                    'type'       => 'ENUM',
                    'constraint' => [
                        'sad',
                        'ok',
                        'happy',
                    ],
                ],
            ]);
            $this->forge->createTable('forge_array_constraint');

            $fields = $this->db->getFieldData('forge_array_constraint');

            $this->assertSame('status', $fields[0]->name);

            if ($this->db->DBDriver === 'SQLite3') {
                // SQLite3 converts array constraints to TEXT CHECK(...)
                $this->assertSame('TEXT', $fields[0]->type);
            } else {
                $this->assertSame('enum', $fields[0]->type);
            }

            $this->forge->dropTable('forge_array_constraint', true);
        } else {
            $this->expectNotToPerformAssertions();
        }
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/4693
     */
    public function testCreateTableWithNullableFieldsGivesNullDataType(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
        ]);

        $createTable = $this->getPrivateMethodInvoker($this->forge, '_createTable');

        $sql = $createTable('forge_nullable_table', false, []);

        if ($this->db->DBDriver !== 'SQLSRV') {
            // @see https://regex101.com/r/bIHVNw/1
            $this->assertMatchesRegularExpression('/(?:`name`|"name") VARCHAR(.*) NULL/', $sql);
        } else {
            // sqlsrv table fields are default nullable
            $this->assertMatchesRegularExpression('/"name" VARCHAR/', $sql);
        }
    }

    public function testCreateTableWithStringField(): void
    {
        $this->forge->dropTable('forge_test_table', true);

        $this->forge->addField('id');
        $this->forge->addField('name varchar(100) NULL');

        $this->forge->createTable('forge_test_table');

        $exist = $this->db->tableExists('forge_test_table');
        $this->forge->dropTable('db_forge_test_table', true);

        $this->assertTrue($exist);
    }

    public function testCreateTableWithEmptyName(): void
    {
        $this->forge->dropTable('forge_test_table', true);

        $this->forge->addField('id');
        $this->forge->addField('name varchar(100) NULL');

        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('A table name is required for that operation.');

        $this->forge->createTable('');
    }

    public function testCreateTableWithNoFields(): void
    {
        $this->forge->dropTable('forge_test_table', true);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Field information is required.');

        $this->forge->createTable('forge_test_table');
    }

    public function testCreateTableWithStringFieldException(): void
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Field information is required for that operation.');

        $this->forge->dropTable('forge_test_table', true);

        $this->forge->addField('id');
        $this->forge->addField('name');

        $this->forge->createTable('forge_test_table');
    }

    public function testRenameTable(): void
    {
        $this->forge->dropTable('forge_test_table_dummy', true);

        $this->forge->addField('id');
        $this->forge->addField('name varchar(100) NULL');

        $this->forge->createTable('forge_test_table');

        $this->forge->renameTable('forge_test_table', 'forge_test_table_dummy');

        $exist = $this->db->tableExists('forge_test_table_dummy');

        $this->assertTrue($exist);
    }

    public function testRenameTableEmptyNameException(): void
    {
        $this->forge->dropTable('forge_test_table_dummy', true);

        $this->forge->addField('id');
        $this->forge->addField('name varchar(100) NULL');

        $this->forge->createTable('forge_test_table');

        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('A table name is required for that operation.');

        $this->forge->renameTable('forge_test_table', '');
    }

    public function testRenameTableNoRenameStatementException(): void
    {
        $this->setPrivateProperty($this->forge, 'renameTableStr', false);

        $this->forge->dropTable('forge_test_table', true);

        $this->forge->addField('id');
        $this->forge->addField('name varchar(100) NULL');

        $this->forge->createTable('forge_test_table');

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('This feature is not available for the database you are using.');

        $this->forge->renameTable('forge_test_table', 'forge_test_table_dummy');
    }

    public function testDropTableWithEmptyName(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('A table name is required for that operation.');

        $this->forge->dropTable('', true);
    }

    public function testForeignKey(): void
    {
        $this->forge->dropTable('forge_test_invoices', true);
        $this->forge->dropTable('forge_test_users', true);

        $attributes = [];

        if ($this->db->DBDriver === 'MySQLi') {
            $attributes = ['ENGINE' => 'InnoDB'];
        }

        $this->forge->addField([
            'id' => [
                'type'       => 'INTEGER',
                'constraint' => 11,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('forge_test_users', true, $attributes);

        $this->forge->addField([
            'id' => [
                'type'       => 'INTEGER',
                'constraint' => 11,
            ],
            'users_id' => [
                'type'       => 'INTEGER',
                'constraint' => 11,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('users_id', 'forge_test_users', 'id', 'CASCADE', 'CASCADE');

        $tableName = 'forge_test_invoices';

        $this->forge->createTable($tableName, true, $attributes);

        $foreignKeyData = $this->db->getForeignKeyData($tableName);

        $foreignKeyName = $this->db->DBPrefix . $tableName . '_users_id_foreign';
        if ($this->db->DBDriver === 'OCI8') {
            $foreignKeyName = $this->db->DBPrefix . $tableName . '_users_id_fk';
        }

        $this->assertSame($foreignKeyData[$foreignKeyName]->constraint_name, $foreignKeyName);
        $this->assertSame($foreignKeyData[$foreignKeyName]->column_name, ['users_id']);
        $this->assertSame($foreignKeyData[$foreignKeyName]->foreign_column_name, ['id']);
        $this->assertSame($foreignKeyData[$foreignKeyName]->table_name, $this->db->DBPrefix . $tableName);
        $this->assertSame($foreignKeyData[$foreignKeyName]->foreign_table_name, $this->db->DBPrefix . 'forge_test_users');

        $this->forge->dropTable($tableName, true);
        $this->forge->dropTable('forge_test_users', true);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/4986
     */
    public function testForeignKeyAddingWithStringFields(): void
    {
        if ($this->db->DBDriver !== 'MySQLi') {
            $this->markTestSkipped('Testing only on MySQLi but fix expands to all DBs.');
        }

        $attributes = ['ENGINE' => 'InnoDB'];

        $this->forge->addField([
            '`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY',
            '`name` VARCHAR(255) NOT NULL',
        ])->createTable('forge_test_users', true, $attributes);

        $foreignKeyName = 'my_custom_fk';

        $this->forge
            ->addField([
                '`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY',
                '`users_id` INT(11) NOT NULL',
                '`name` VARCHAR(255) NOT NULL',
            ])
            ->addForeignKey('users_id', 'forge_test_users', 'id', 'CASCADE', 'CASCADE', $foreignKeyName)
            ->createTable('forge_test_invoices', true, $attributes);

        $foreignKeyData = $this->db->getForeignKeyData('forge_test_invoices');

        $this->assertSame($foreignKeyName, $foreignKeyData[$foreignKeyName]->constraint_name);
        $this->assertSame(['users_id'], $foreignKeyData[$foreignKeyName]->column_name);
        $this->assertSame(['id'], $foreignKeyData[$foreignKeyName]->foreign_column_name);
        $this->assertSame($this->db->DBPrefix . 'forge_test_invoices', $foreignKeyData[$foreignKeyName]->table_name);
        $this->assertSame($this->db->DBPrefix . 'forge_test_users', $foreignKeyData[$foreignKeyName]->foreign_table_name);

        $this->forge->dropTable('forge_test_invoices', true);
        $this->forge->dropTable('forge_test_users', true);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/4310
     */
    public function testCompositeForeignKey(): void
    {
        $this->forge->dropTable('forge_test_invoices', true);
        $this->forge->dropTable('forge_test_users', true);

        $attributes = [];

        if ($this->db->DBDriver === 'MySQLi') {
            $attributes = ['ENGINE' => 'InnoDB'];
        }

        $this->forge->addField([
            'id' => [
                'type'       => 'INTEGER',
                'constraint' => 11,
            ],
            'second_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
        ]);
        $this->forge->addPrimaryKey(['id', 'second_id']);
        $this->forge->createTable('forge_test_users', true, $attributes);

        $fields = [
            'id' => [
                'type'       => 'INTEGER',
                'constraint' => 11,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'users_id' => [
                'type'       => 'INTEGER',
                'constraint' => 11,
            ],
            'users_second_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
        ];

        $this->forge->addField($fields);
        $this->forge->addPrimaryKey('id');

        $foreignKeyName = 'my_custom_fk';

        if ($this->db->DBDriver === 'SQLite3') {
            $foreignKeyName = $this->db->DBPrefix . 'forge_test_invoices_users_id_users_second_id_foreign';
        }

        $this->forge->addForeignKey(['users_id', 'users_second_id'], 'forge_test_users', ['id', 'second_id'], 'CASCADE', 'CASCADE', ($this->db->DBDriver !== 'SQLite3' ? $foreignKeyName : ''));

        $this->forge->createTable('forge_test_invoices', true, $attributes);

        $foreignKeyData = $this->db->getForeignKeyData('forge_test_invoices');

        $haystack = ['users_id', 'users_second_id'];
        $this->assertSame($foreignKeyName, $foreignKeyData[$foreignKeyName]->constraint_name);
        $this->assertSame($foreignKeyData[$foreignKeyName]->column_name, $haystack);

        $this->assertSame($this->db->DBPrefix . 'forge_test_invoices', $foreignKeyData[$foreignKeyName]->table_name);
        $this->assertSame($this->db->DBPrefix . 'forge_test_users', $foreignKeyData[$foreignKeyName]->foreign_table_name);

        $this->forge->dropTable('forge_test_invoices', true);
        $this->forge->dropTable('forge_test_users', true);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/4310
     */
    public function testCompositeForeignKeyFieldNotExistException(): void
    {
        $this->expectException(DatabaseException::class);
        if ($this->db->DBDriver === 'SQLite3') {
            $this->expectExceptionMessage('SQLite does not support foreign key names. CodeIgniter will refer to them in the format: prefix_table_column_referencecolumn_foreign');
        } else {
            $this->expectExceptionMessage('Field "user_id, user_second_id" not found.');
        }

        $attributes = [];

        if ($this->db->DBDriver === 'MySQLi') {
            $attributes = ['ENGINE' => 'InnoDB'];
        }

        $this->forge->addField([
            'id' => [
                'type'       => 'INTEGER',
                'constraint' => 11,
            ],
            'second_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
        ]);
        $this->forge->addPrimaryKey(['id', 'second_id']);
        $this->forge->createTable('forge_test_users', true, $attributes);

        $this->forge->addField([
            'id' => [
                'type'       => 'INTEGER',
                'constraint' => 11,
            ],
            'users_id' => [
                'type'       => 'INTEGER',
                'constraint' => 11,
            ],
            'users_second_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
        ]);
        $this->forge->addKey('id', true);

        $foreignKeyName = 'forge_test_invoices_fk';

        $this->forge->addForeignKey(['user_id', 'user_second_id'], 'forge_test_users', ['id', 'second_id'], 'CASCADE', 'CASCADE', $foreignKeyName);

        $this->forge->createTable('forge_test_invoices', true, $attributes);
    }

    public function testForeignKeyFieldNotExistException(): void
    {
        $this->expectException(DatabaseException::class);
        if ($this->db->DBDriver === 'SQLite3') {
            $this->expectExceptionMessage('SQLite does not support foreign key names. CodeIgniter will refer to them in the format: prefix_table_column_referencecolumn_foreign');
        } else {
            $this->expectExceptionMessage('Field "user_id" not found.');
        }

        $attributes = [];

        if ($this->db->DBDriver === 'MySQLi') {
            $attributes = ['ENGINE' => 'InnoDB'];
        }

        $this->forge->addField([
            'id' => [
                'type'       => 'INTEGER',
                'constraint' => 11,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('forge_test_users', true, $attributes);

        $this->forge->addField([
            'id' => [
                'type'       => 'INTEGER',
                'constraint' => 11,
            ],
            'users_id' => [
                'type'       => 'INTEGER',
                'constraint' => 11,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
        ]);
        $this->forge->addKey('id', true);

        $foreignKeyName = 'forge_test_invoices_fk';

        $this->forge->addForeignKey('user_id', 'forge_test_users', 'id', 'CASCADE', 'CASCADE', $foreignKeyName);

        $this->forge->createTable('forge_test_invoices', true, $attributes);
    }

    public function testDropForeignKey(): void
    {
        $this->forge->dropTable('forge_test_invoices', true);
        $this->forge->dropTable('forge_test_users', true);

        $attributes = [];

        if ($this->db->DBDriver === 'MySQLi') {
            $attributes = ['ENGINE' => 'InnoDB'];
        }

        $this->forge->addField([
            'id' => [
                'type'       => 'INTEGER',
                'constraint' => 11,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('forge_test_users', true, $attributes);

        $this->forge->addField([
            'id' => [
                'type'       => 'INTEGER',
                'constraint' => 11,
            ],
            'users_id' => [
                'type'       => 'INTEGER',
                'constraint' => 11,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
        ]);
        $this->forge->addKey('id', true);

        $foreignKeyName = 'forge_test_invoices_fk';

        if ($this->db->DBDriver === 'SQLite3') {
            $foreignKeyName = $this->db->DBPrefix . 'forge_test_invoices_users_id_foreign';
        }

        $this->forge->addForeignKey('users_id', 'forge_test_users', 'id', 'CASCADE', 'CASCADE', ($this->db->DBDriver !== 'SQLite3' ? $foreignKeyName : ''));

        $tableName = 'forge_test_invoices';

        $this->forge->createTable($tableName, true, $attributes);

        $this->forge->dropForeignKey($tableName, $foreignKeyName);

        $foreignKeyData = $this->db->getForeignKeyData($tableName);

        $this->assertEmpty($foreignKeyData);

        $this->forge->dropTable($tableName, true);
        $this->forge->dropTable('forge_test_users', true);
    }

    public function testAddColumn(): void
    {
        $this->forge->dropTable('forge_test_table', true);

        $this->forge->addField([
            'id' => [
                'type'           => 'INTEGER',
                'constraint'     => 11,
                'unsigned'       => false,
                'auto_increment' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('forge_test_table');

        $newField = [
            'username' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'unique'     => false,
            ],
        ];

        $this->forge->addColumn('forge_test_table', $newField);

        $fieldNames = $this->db->table('forge_test_table')
            ->get()
            ->getFieldNames();

        $this->forge->dropTable('forge_test_table', true);

        $this->assertSame('username', $fieldNames[1]);
    }

    public function testAddColumnNull()
    {
        $this->forge->dropTable('forge_test_table', true);

        $this->forge->addField([
            'col1' => ['type' => 'VARCHAR', 'constraint' => 255],
            'col2' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'col3' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
        ]);
        $this->forge->createTable('forge_test_table');

        $this->forge->addColumn('forge_test_table', [
            'col4' => ['type' => 'VARCHAR', 'constraint' => 255],
            'col5' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'col6' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
        ]);

        $this->db->resetDataCache();

        $col1 = $this->getMetaData('col1', 'forge_test_table');
        $this->assertFalse($col1->nullable);
        $col2 = $this->getMetaData('col2', 'forge_test_table');
        $this->assertTrue($col2->nullable);
        $col3 = $this->getMetaData('col3', 'forge_test_table');
        $this->assertFalse($col3->nullable);
        $col4 = $this->getMetaData('col4', 'forge_test_table');
        $this->assertTrue($col4->nullable);
        $col5 = $this->getMetaData('col5', 'forge_test_table');
        $this->assertTrue($col5->nullable);
        $col6 = $this->getMetaData('col6', 'forge_test_table');
        $this->assertFalse($col6->nullable);

        $this->forge->dropTable('forge_test_table', true);
    }

    public function testAddFields(): void
    {
        $tableName = 'forge_test_fields';
        if ($this->db->DBDriver === 'OCI8') {
            $tableName = 'getestfield';
        }
        $this->forge->dropTable($tableName, true);

        $this->forge->addField([
            'id' => [
                'type'           => 'INTEGER',
                'constraint'     => 11,
                'unsigned'       => false,
                'auto_increment' => true,
            ],
            'username' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'unique'     => false,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'active' => [
                'type'       => 'INTEGER',
                'constraint' => 11,
                'default'    => 0,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['username', 'active']);
        $this->forge->createTable($tableName, true);

        $fieldsNames = $this->db->getFieldNames($tableName);
        $fieldsData  = $this->db->getFieldData($tableName);

        $this->forge->dropTable($tableName, true);

        $this->assertIsArray($fieldsNames);

        if ($this->db->DBDriver === 'MySQLi') {
            $expected = [
                0 => [
                    'name'        => 'id',
                    'type'        => 'int',
                    'max_length'  => 11,
                    'nullable'    => false,
                    'default'     => null,
                    'primary_key' => 1,
                ],
                1 => [
                    'name'        => 'username',
                    'type'        => 'varchar',
                    'max_length'  => 255,
                    'nullable'    => false,
                    'default'     => null,
                    'primary_key' => 0,
                ],
                2 => [
                    'name'        => 'name',
                    'type'        => 'varchar',
                    'max_length'  => 255,
                    'nullable'    => true,
                    'default'     => null,
                    'primary_key' => 0,
                ],
                3 => [
                    'name'        => 'active',
                    'type'        => 'int',
                    'max_length'  => 11,
                    'nullable'    => false,
                    'default'     => '0',
                    'primary_key' => 0,
                ],
            ];

            if (version_compare($this->db->getVersion(), '8.0.17', '>=') && strpos($this->db->getVersion(), 'MariaDB') === false) {
                // As of MySQL 8.0.17, the display width attribute for integer data types
                // is deprecated and is not reported back anymore.
                // @see https://dev.mysql.com/doc/refman/8.0/en/numeric-type-attributes.html
                $expected[0]['max_length'] = null;
                $expected[3]['max_length'] = null;
            }
        } elseif ($this->db->DBDriver === 'Postgre') {
            $expected = [
                0 => [
                    'name'       => 'id',
                    'type'       => 'integer',
                    'nullable'   => false,
                    'default'    => "nextval('db_forge_test_fields_id_seq'::regclass)",
                    'max_length' => '32',
                ],
                1 => [
                    'name'       => 'username',
                    'type'       => 'character varying',
                    'nullable'   => false,
                    'default'    => null,
                    'max_length' => '255',
                ],
                2 => [
                    'name'       => 'name',
                    'type'       => 'character varying',
                    'nullable'   => true,
                    'default'    => null,
                    'max_length' => '255',
                ],
                3 => [
                    'name'       => 'active',
                    'type'       => 'integer',
                    'nullable'   => false,
                    'default'    => '0',
                    'max_length' => '32',
                ],
            ];
        } elseif ($this->db->DBDriver === 'SQLite3') {
            $expected = [
                0 => [
                    'name'        => 'id',
                    'type'        => 'INTEGER',
                    'max_length'  => null,
                    'default'     => null,
                    'primary_key' => true,
                    'nullable'    => true,
                ],
                1 => [
                    'name'        => 'username',
                    'type'        => 'VARCHAR',
                    'max_length'  => null,
                    'default'     => null,
                    'primary_key' => false,
                    'nullable'    => false,
                ],
                2 => [
                    'name'        => 'name',
                    'type'        => 'VARCHAR',
                    'max_length'  => null,
                    'default'     => null,
                    'primary_key' => false,
                    'nullable'    => true,
                ],
                3 => [
                    'name'        => 'active',
                    'type'        => 'INTEGER',
                    'max_length'  => null,
                    'default'     => '0',
                    'primary_key' => false,
                    'nullable'    => false,
                ],
            ];
        } elseif ($this->db->DBDriver === 'SQLSRV') {
            $expected = [
                0 => [
                    'name'       => 'id',
                    'type'       => 'int',
                    'default'    => null,
                    'max_length' => 10,
                    'nullable'   => false,
                ],
                1 => [
                    'name'       => 'username',
                    'type'       => 'varchar',
                    'default'    => null,
                    'max_length' => 255,
                    'nullable'   => false,
                ],
                2 => [
                    'name'       => 'name',
                    'type'       => 'varchar',
                    'default'    => null,
                    'max_length' => 255,
                    'nullable'   => true,
                ],
                3 => [
                    'name'       => 'active',
                    'type'       => 'int',
                    'default'    => '((0))', // Why?
                    'max_length' => 10,
                    'nullable'   => false,
                ],
            ];
        } elseif ($this->db->DBDriver === 'OCI8') {
            $expected = [
                0 => [
                    'name'       => 'id',
                    'type'       => 'NUMBER',
                    'max_length' => '11',
                    'default'    => '"ORACLE"."ISEQ$$_80229".nextval', // Sequence id may change
                    'nullable'   => false,
                ],
                1 => [
                    'name'       => 'username',
                    'type'       => 'VARCHAR2',
                    'max_length' => '255',
                    'default'    => '',
                    'nullable'   => false,
                ],
                2 => [
                    'name'       => 'name',
                    'type'       => 'VARCHAR2',
                    'max_length' => '255',
                    'default'    => null,
                    'nullable'   => true,
                ],
                3 => [
                    'name'       => 'active',
                    'type'       => 'NUMBER',
                    'max_length' => '11',
                    'default'    => '0 ', // Why?
                    'nullable'   => false,
                ],
            ];

            // Sequence id may change - MAY USE "SYSTEM" instead of "ORACLE"
            $this->assertMatchesRegularExpression('/"(ORACLE|SYSTEM)"."ISEQ\\$\\$_\d+".nextval/', $fieldsData[0]->default);
            $expected[0]['default'] = $fieldsData[0]->default;
        } else {
            $this->fail(sprintf('DB driver "%s" is not supported.', $this->db->DBDriver));
        }

        $this->assertSame($expected, json_decode(json_encode($fieldsData), true));
    }

    public function testCompositeKey(): void
    {
        $this->forge->dropTable('forge_test_1', true);

        $this->forge->addField([
            'id' => [
                'type'           => 'INTEGER',
                'constraint'     => 3,
                'auto_increment' => true,
            ],
            'code' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
            ],
            'company' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
            ],
            'active' => [
                'type'       => 'INTEGER',
                'constraint' => 1,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['code', 'company']);
        $this->forge->addUniqueKey(['code', 'active']);
        $this->forge->createTable('forge_test_1', true);

        $keys = $this->db->getIndexData('forge_test_1');

        if ($this->db->DBDriver === 'MySQLi') {
            $this->assertSame($keys['PRIMARY']->name, 'PRIMARY');
            $this->assertSame($keys['PRIMARY']->fields, ['id']);
            $this->assertSame($keys['PRIMARY']->type, 'PRIMARY');

            $this->assertSame($keys['code_company']->name, 'code_company');
            $this->assertSame($keys['code_company']->fields, ['code', 'company']);
            $this->assertSame($keys['code_company']->type, 'INDEX');

            $this->assertSame($keys['code_active']->name, 'code_active');
            $this->assertSame($keys['code_active']->fields, ['code', 'active']);
            $this->assertSame($keys['code_active']->type, 'UNIQUE');
        } elseif ($this->db->DBDriver === 'Postgre') {
            $this->assertSame($keys['pk_db_forge_test_1']->name, 'pk_db_forge_test_1');
            $this->assertSame($keys['pk_db_forge_test_1']->fields, ['id']);
            $this->assertSame($keys['pk_db_forge_test_1']->type, 'PRIMARY');

            $this->assertSame($keys['db_forge_test_1_code_company']->name, 'db_forge_test_1_code_company');
            $this->assertSame($keys['db_forge_test_1_code_company']->fields, ['code', 'company']);
            $this->assertSame($keys['db_forge_test_1_code_company']->type, 'INDEX');

            $this->assertSame($keys['db_forge_test_1_code_active']->name, 'db_forge_test_1_code_active');
            $this->assertSame($keys['db_forge_test_1_code_active']->fields, ['code', 'active']);
            $this->assertSame($keys['db_forge_test_1_code_active']->type, 'UNIQUE');
        } elseif ($this->db->DBDriver === 'SQLite3') {
            $this->assertSame($keys['PRIMARY']->name, 'PRIMARY');
            $this->assertSame($keys['PRIMARY']->fields, ['id']);
            $this->assertSame($keys['PRIMARY']->type, 'PRIMARY');

            $this->assertSame($keys['db_forge_test_1_code_company']->name, 'db_forge_test_1_code_company');
            $this->assertSame($keys['db_forge_test_1_code_company']->fields, ['code', 'company']);
            $this->assertSame($keys['db_forge_test_1_code_company']->type, 'INDEX');

            $this->assertSame($keys['db_forge_test_1_code_active']->name, 'db_forge_test_1_code_active');
            $this->assertSame($keys['db_forge_test_1_code_active']->fields, ['code', 'active']);
            $this->assertSame($keys['db_forge_test_1_code_active']->type, 'UNIQUE');
        } elseif ($this->db->DBDriver === 'SQLSRV') {
            $this->assertSame($keys['pk_db_forge_test_1']->name, 'pk_db_forge_test_1');
            $this->assertSame($keys['pk_db_forge_test_1']->fields, ['id']);
            $this->assertSame($keys['pk_db_forge_test_1']->type, 'PRIMARY');

            $this->assertSame($keys['db_forge_test_1_code_company']->name, 'db_forge_test_1_code_company');
            $this->assertSame($keys['db_forge_test_1_code_company']->fields, ['code', 'company']);
            $this->assertSame($keys['db_forge_test_1_code_company']->type, 'INDEX');

            $this->assertSame($keys['db_forge_test_1_code_active']->name, 'db_forge_test_1_code_active');
            $this->assertSame($keys['db_forge_test_1_code_active']->fields, ['code', 'active']);
            $this->assertSame($keys['db_forge_test_1_code_active']->type, 'UNIQUE');
        } elseif ($this->db->DBDriver === 'OCI8') {
            $this->assertSame($keys['pk_db_forge_test_1']->name, 'pk_db_forge_test_1');
            $this->assertSame($keys['pk_db_forge_test_1']->fields, ['id']);
            $this->assertSame($keys['pk_db_forge_test_1']->type, 'PRIMARY');

            $this->assertSame($keys['db_forge_test_1_code_company']->name, 'db_forge_test_1_code_company');
            $this->assertSame($keys['db_forge_test_1_code_company']->fields, ['code', 'company']);
            $this->assertSame($keys['db_forge_test_1_code_company']->type, 'INDEX');

            $this->assertSame($keys['db_forge_test_1_code_active']->name, 'db_forge_test_1_code_active');
            $this->assertSame($keys['db_forge_test_1_code_active']->fields, ['code', 'active']);
            $this->assertSame($keys['db_forge_test_1_code_active']->type, 'UNIQUE');
        }

        $this->forge->dropTable('forge_test_1', true);
    }

    public function testSetKeyNames(): void
    {
        $this->forge->dropTable('forge_test_1', true);

        $this->forge->addField([
            'id' => [
                'type'           => 'INTEGER',
                'constraint'     => 3,
                'auto_increment' => true,
            ],
            'code' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
            ],
            'company' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
            ],
            'active' => [
                'type'       => 'INTEGER',
                'constraint' => 1,
            ],
        ]);

        $pk          = 'my_custom_pk';
        $index       = 'my_custom_index';
        $uniqueIndex = 'my_custom_unique_index';

        if ($this->db->DBDriver === 'MySQLi' || $this->db->DBDriver === 'SQLite3') {
            $pk = 'PRIMARY';
        }

        $this->forge->addPrimaryKey('id', $pk);
        $this->forge->addKey(['code', 'company'], false, false, $index);
        $this->forge->addUniqueKey(['code', 'active'], $uniqueIndex);
        $this->forge->createTable('forge_test_1', true);

        $keys = $this->db->getIndexData('forge_test_1');

        // mysql must redefine auto increment which can only exist on a key
        if ($this->db->DBDriver === 'MySQLi') {
            $id = [
                'id' => [
                    'name'       => 'id',
                    'type'       => 'INTEGER',
                    'constraint' => 3,
                ],
            ];
            $this->forge->modifyColumn('forge_test_1', $id);
        }

        $this->assertSame($keys[$pk]->name, $pk);
        $this->assertSame($keys[$index]->name, $index);
        $this->assertSame($keys[$uniqueIndex]->name, $uniqueIndex);

        $this->forge->dropPrimaryKey('forge_test_1', $pk);
        $this->forge->dropKey('forge_test_1', $index, false);
        $this->forge->dropKey('forge_test_1', $uniqueIndex, false);

        $this->assertCount(0, $this->db->getIndexData('forge_test_1'));

        $this->forge->dropTable('forge_test_1', true);
    }

    public function testDropColumn(): void
    {
        $this->forge->dropTable('forge_test_two', true);

        $this->forge->addField([
            'id' => [
                'type'           => 'INTEGER',
                'constraint'     => 11,
                'unsigned'       => false,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'varchar',
                'constraint' => 255,
                'null'       => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('forge_test_two');

        $this->assertTrue($this->db->fieldExists('name', 'forge_test_two'));

        $this->forge->dropColumn('forge_test_two', 'name');

        $this->db->resetDataCache();

        $this->assertFalse($this->db->fieldExists('name', 'forge_test_two'));

        $this->forge->dropTable('forge_test_two', true);
    }

    public function testModifyColumnRename(): void
    {
        $this->forge->dropTable('forge_test_three', true);

        $this->forge->addField([
            'id' => [
                'type'           => 'INTEGER',
                'constraint'     => 11,
                'unsigned'       => false,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'varchar',
                'constraint' => 255,
                'null'       => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('forge_test_three');

        $this->assertTrue($this->db->fieldExists('name', 'forge_test_three'));

        $this->forge->modifyColumn('forge_test_three', [
            'name' => [
                'name'       => 'altered',
                'type'       => 'varchar',
                'constraint' => 255,
                'null'       => true,
            ],
        ]);

        $this->db->resetDataCache();

        $this->assertFalse($this->db->fieldExists('name', 'forge_test_three'));
        $this->assertTrue($this->db->fieldExists('altered', 'forge_test_three'));

        $this->forge->dropTable('forge_test_three', true);
    }

    public function testModifyColumnNullTrue(): void
    {
        $this->forge->dropTable('forge_test_modify', true);

        $this->forge->addField([
            'col1' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'col2' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'col3' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
        ]);
        $this->forge->createTable('forge_test_modify');

        $this->forge->modifyColumn('forge_test_modify', [
            'col1' => ['type' => 'VARCHAR', 'constraint' => 1],
            'col2' => ['type' => 'VARCHAR', 'constraint' => 1, 'null' => true],
            'col3' => ['type' => 'VARCHAR', 'constraint' => 1, 'null' => false],
        ]);

        $this->db->resetDataCache();

        $col1 = $this->getMetaData('col1', 'forge_test_modify');
        $this->assertTrue($col1->nullable);
        $col2 = $this->getMetaData('col2', 'forge_test_modify');
        $this->assertTrue($col2->nullable);
        $col3 = $this->getMetaData('col3', 'forge_test_modify');
        $this->assertFalse($col3->nullable);

        $this->forge->dropTable('forge_test_modify', true);
    }

    public function testModifyColumnNullFalse(): void
    {
        $this->forge->dropTable('forge_test_modify', true);

        $this->forge->addField([
            'col1' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'col2' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'col3' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
        ]);
        $this->forge->createTable('forge_test_modify');

        $this->forge->modifyColumn('forge_test_modify', [
            'col1' => ['type' => 'VARCHAR', 'constraint' => 1],
            'col2' => ['type' => 'VARCHAR', 'constraint' => 1, 'null' => true],
            'col3' => ['type' => 'VARCHAR', 'constraint' => 1, 'null' => false],
        ]);

        $this->db->resetDataCache();

        $col1 = $this->getMetaData('col1', 'forge_test_modify');
        $this->assertTrue($col1->nullable); // Nullable by default.
        $col2 = $this->getMetaData('col2', 'forge_test_modify');
        $this->assertTrue($col2->nullable);
        $col3 = $this->getMetaData('col3', 'forge_test_modify');
        $this->assertFalse($col3->nullable);

        $this->forge->dropTable('forge_test_modify', true);
    }

    private function getMetaData(string $column, string $table): stdClass
    {
        $fields = $this->db->getFieldData($table);

        $name = array_search(
            $column,
            array_column($fields, 'name'),
            true
        );

        if ($name === false) {
            throw new LogicException('Column not found: ' . $column);
        }

        return $fields[$name];
    }

    public function testConnectWithArrayGroup(): void
    {
        $group = config('Database');
        $group = $group->tests;

        $forge = Database::forge($group);

        $this->assertInstanceOf(Forge::class, $forge);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1983
     */
    public function testDropTableSuccess(): void
    {
        // Add an index to user table so we have
        // something to work with
        $this->forge->addField([
            'id' => [
                'type'       => 'INTEGER',
                'constraint' => 3,
            ],
        ]);
        $this->forge->addKey('id');
        $this->forge->createTable('droptest');

        $this->assertCount(1, $this->db->getIndexData('droptest'));

        $this->forge->dropTable('droptest', true);

        $this->assertFalse($this->db->tableExists('dropTest'));

        if ($this->db->DBDriver === 'SQLite3') {
            $this->assertCount(0, $this->db->getIndexData('droptest'));
        }
    }

    public function testDropMultipleColumnWithArray(): void
    {
        $this->forge->dropTable('forge_test_two', true);

        $this->forge->addField([
            'id' => [
                'type'           => 'INTEGER',
                'constraint'     => 11,
                'unsigned'       => false,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'varchar',
                'constraint' => 255,
                'null'       => true,
            ],
            'email' => [
                'type'       => 'varchar',
                'constraint' => 255,
                'null'       => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('forge_test_two');

        $this->assertTrue($this->db->fieldExists('name', 'forge_test_two'));

        $this->forge->dropColumn('forge_test_two', ['id', 'name']);

        $this->db->resetDataCache();

        $this->assertFalse($this->db->fieldExists('id', 'forge_test_two'));
        $this->assertFalse($this->db->fieldExists('name', 'forge_test_two'));

        $this->forge->dropTable('forge_test_two', true);
    }

    public function testDropMultipleColumnWithString(): void
    {
        $this->forge->dropTable('forge_test_four', true);

        $this->forge->addField([
            'id' => [
                'type'           => 'INTEGER',
                'constraint'     => 11,
                'unsigned'       => false,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'varchar',
                'constraint' => 255,
                'null'       => true,
            ],
            'email' => [
                'type'       => 'varchar',
                'constraint' => 255,
                'null'       => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('forge_test_four');

        $this->assertTrue($this->db->fieldExists('name', 'forge_test_four'));

        $this->forge->dropColumn('forge_test_four', 'id, name');

        $this->db->resetDataCache();

        $this->assertFalse($this->db->fieldExists('id', 'forge_test_four'));
        $this->assertFalse($this->db->fieldExists('name', 'forge_test_four'));

        $this->forge->dropTable('forge_test_four', true);
    }

    public function testDropKey(): void
    {
        $this->forge->dropTable('key_test_users', true);
        $keyName = 'key_test_users_id';

        $attributes = [];

        if ($this->db->DBDriver === 'MySQLi') {
            $keyName    = 'id';
            $attributes = ['ENGINE' => 'InnoDB'];
        }

        $this->forge->addField([
            'id' => [
                'type'       => 'INTEGER',
                'constraint' => 11,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
        ]);
        $this->forge->addKey('id');
        $this->forge->createTable('key_test_users', true, $attributes);

        $this->forge->dropKey('key_test_users', $keyName);

        $foreignKeyData = $this->db->getIndexData('key_test_users');

        $this->assertEmpty($foreignKeyData);

        $this->forge->dropTable('key_test_users', true);
    }

    public function testAddTextColumnWithConstraint(): void
    {
        // some DBMS do not allow a constraint for type TEXT
        $this->forge->addColumn('user', [
            'text_with_constraint' => ['type' => 'text', 'constraint' => 255, 'default' => ''],
        ]);

        $this->assertTrue($this->db->fieldExists('text_with_constraint', 'user'));

        // SQLSRV requires dropping default constraint before dropping column
        $this->forge->dropColumn('user', 'text_with_constraint');

        $this->db->resetDataCache();

        $this->assertFalse($this->db->fieldExists('text_with_constraint', 'user'));
    }

    public function testDropPrimaryKey(): void
    {
        $this->forge->dropTable('forge_test_users', true);

        $this->forge->addField([
            'id' => [
                'type'       => 'INTEGER',
                'constraint' => 11,
            ],
            'second_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
        ]);
        $primaryKeys = ['id', 'second_id'];
        $this->forge->addPrimaryKey($primaryKeys);
        $this->forge->createTable('forge_test_users', true);

        $indexes = $this->db->getIndexData('forge_test_users');

        $this->assertCount(1, $indexes);
        $this->assertSame($primaryKeys, current($indexes)->fields);

        $this->forge->dropPrimaryKey('forge_test_users');

        $indexes = $this->db->getIndexData('forge_test_users');

        $this->assertCount(0, $indexes);

        $this->forge->dropTable('forge_test_users', true);
    }

    public function testProcessIndexes(): void
    {
        // make sure tables don't exist
        $this->forge->dropTable('actions', true);
        $this->forge->dropTable('user2', true);

        $this->createUser2TableWithKeys();
        $this->populateUser2Table();
        $this->createActionsTable();

        // define indexes, primary key, and foreign keys on existing table
        $this->forge->addKey('name', false, false, 'db_actions_name');
        $this->forge->addKey(['category', 'name'], false, true, 'db_actions_category_name');
        $this->forge->addPrimaryKey('id');

        // SQLite does not support custom foreign key name
        if ($this->db->DBDriver === 'SQLite3') {
            $this->forge->addForeignKey('userid', 'user', 'id');
            $this->forge->addForeignKey('userid2', 'user2', 'id');
        } else {
            $this->forge->addForeignKey('userid', 'user', 'id', '', '', 'db_actions_userid_foreign');
            $this->forge->addForeignKey('userid2', 'user2', 'id', '', '', 'db_actions_userid2_foreign');
        }

        // create indexes
        $this->forge->processIndexes('actions');

        // get a list of all indexes
        $allIndexes = $this->db->getIndexData('actions');

        // check that db_actions_name key exists
        $indexes = array_filter(
            $allIndexes,
            static fn ($index) => ($index->name === 'db_actions_name')
                    && ($index->fields === [0 => 'name'])
        );
        $this->assertCount(1, $indexes);

        // check that db_actions_category_name key exists
        $indexes = array_filter(
            $allIndexes,
            static fn ($index) => ($index->name === 'db_actions_category_name')
                    && ($index->fields === [0 => 'category', 1 => 'name'])
        );
        $this->assertCount(1, $indexes);

        // check that the primary key exists
        $indexes = array_filter(
            $allIndexes,
            static fn ($index) => $index->type === 'PRIMARY'
        );
        $this->assertCount(1, $indexes);

        // check that the two foreign keys exist
        $this->assertCount(2, $this->db->getForeignKeyData('actions'));

        // test inserting data
        $this->insertDataTest();

        // drop tables to avoid any future conflicts
        $this->forge->dropTable('actions', true);
        $this->forge->dropTable('user2', true);
    }

    private function createUser2TableWithKeys(): void
    {
        $fields = [
            'id'         => ['type' => 'INTEGER', 'constraint' => 3, 'auto_increment' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 80],
            'email'      => ['type' => 'VARCHAR', 'constraint' => 100],
            'country'    => ['type' => 'VARCHAR', 'constraint' => 40],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ];
        $this->forge->addField($fields)
            ->addKey('id', true)
            ->addUniqueKey('email')
            ->addKey('country')
            ->createTable('user2', true);
    }

    private function populateUser2Table(): void
    {
        $data = [
            [
                'name'    => 'Derek Jones2',
                'email'   => 'derek@world.com',
                'country' => 'France',
            ],
            [
                'name'    => 'Ahmadinejad2',
                'email'   => 'ahmadinejad@world.com',
                'country' => 'Greece',
            ],
            [
                'name'    => 'Richard A Causey2',
                'email'   => 'richard@world.com',
                'country' => 'France',
            ],
            [
                'name'    => 'Chris Martin2',
                'email'   => 'chris@world.com',
                'country' => 'Greece',
            ],
        ];
        $this->db->table('user2')->insertBatch($data);
    }

    private function createActionsTable(): void
    {
        $fields = [
            'id'       => ['type' => 'int', 'constraint' => 9],
            'userid'   => ['type' => 'int', 'constraint' => 9],
            'userid2'  => ['type' => 'int', 'constraint' => 9],
            'category' => ['type' => 'varchar', 'constraint' => 63],
            'name'     => ['type' => 'varchar', 'constraint' => 63],
        ];
        $this->forge->addField($fields);
        $this->forge->createTable('actions');
    }

    private function insertDataTest(): void
    {
        $data = [
            [
                'id'       => 1,
                'name'     => 'test name',
                'category' => 'cat',
                'userid'   => 1,
                'userid2'  => 1,
            ],
            [
                'id'       => 2,
                'name'     => 'another name',
                'category' => 'cat',
                'userid'   => 2,
                'userid2'  => 2,
            ],
        ];
        $this->db->table('actions')->insertBatch($data);

        // check that first row of data was inserted
        $this->seeInDatabase('actions', [
            'id'      => 1,
            'name'    => 'test name',
            'userid'  => '1',
            'userid2' => '1',
        ]);

        // check that second row of data was inserted
        $this->seeInDatabase('actions', [
            'id'      => 2,
            'name'    => 'another name',
            'userid'  => '2',
            'userid2' => '2',
        ]);
    }
}
