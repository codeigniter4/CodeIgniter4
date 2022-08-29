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
use RuntimeException;
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

        /* when running locally if one of these tables isn't dropped it may cause error
        $this->forge->dropTable('forge_test_invoices', true);
        $this->forge->dropTable('forge_test_inv', true);
        $this->forge->dropTable('forge_test_users', true);
        $this->forge->dropTable('actions', true);
        */

        parent::setUp();
    }

    public function testCreateDatabase()
    {
        if ($this->db->DBDriver === 'OCI8') {
            $this->markTestSkipped('OCI8 does not support create database.');
        }
        $databaseCreated = $this->forge->createDatabase('test_forge_database');

        $this->assertTrue($databaseCreated);
    }

    public function testCreateDatabaseIfNotExists()
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

    public function testCreateDatabaseIfNotExistsWithDb()
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

    public function testDropDatabase()
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

    public function testCreateDatabaseExceptionNoCreateStatement()
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

    public function testDropDatabaseExceptionNoDropStatement()
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

    public function testCreateTable()
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

    public function testCreateTableWithExists()
    {
        // create table so that it exists in database
        $this->forge->addField([
            'id'   => ['type' => 'INTEGER', 'constraint' => 3, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 80],
        ])->addKey('id', true)->createTable('test_exists', true);

        // table exists in cache
        $this->assertTrue($this->forge->getConnection()->tableExists('db_test_exists', true));

        // table exists without cached results
        $this->assertTrue($this->forge->getConnection()->tableExists('db_test_exists', false));

        // try creating table when table exists
        $result = $this->forge->addField([
            'id'   => ['type' => 'INTEGER', 'constraint' => 3, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 80],
        ])->addKey('id', true)->createTable('test_exists', true);

        $this->assertTrue($result);

        // Delete table outside of forge. This should leave table in cache as existing.
        $this->forge->getConnection()->query('DROP TABLE ' . $this->forge->getConnection()->protectIdentifiers('db_test_exists', true, null, false));

        // table stil exists in cache
        $this->assertTrue($this->forge->getConnection()->tableExists('db_test_exists', true));

        // table does not exist without cached results - this will update the cache
        $this->assertFalse($this->forge->getConnection()->tableExists('db_test_exists', false));

        // the call above should update the cache - table should not exist in cache anymore
        $this->assertFalse($this->forge->getConnection()->tableExists('db_test_exists', true));

        // try creating table when table does not exist but still in cache
        $result = $this->forge->addField([
            'id'   => ['type' => 'INTEGER', 'constraint' => 3, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 80],
        ])->addKey('id', true)->createTable('test_exists', true);

        $this->assertTrue($result);

        // check that the table does now exist without cached results
        $this->assertTrue($this->forge->getConnection()->tableExists('db_test_exists', false));

        // drop table so that it doesn't mess up other tests
        $this->forge->dropTable('test_exists');
    }

    public function testCreateTableApplyBigInt()
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

    public function testCreateTableWithAttributes()
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

    public function testCreateTableWithArrayFieldConstraints()
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

    public function testCreateTableWithStringField()
    {
        $this->forge->dropTable('forge_test_table', true);

        $this->forge->addField('id');
        $this->forge->addField('name varchar(100) NULL');

        $this->forge->createTable('forge_test_table');

        $exist = $this->db->tableExists('forge_test_table');
        $this->forge->dropTable('db_forge_test_table', true);

        $this->assertTrue($exist);
    }

    public function testCreateTableWithEmptyName()
    {
        $this->forge->dropTable('forge_test_table', true);

        $this->forge->addField('id');
        $this->forge->addField('name varchar(100) NULL');

        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('A table name is required for that operation.');

        $this->forge->createTable('');
    }

    public function testCreateTableWithNoFields()
    {
        $this->forge->dropTable('forge_test_table', true);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Field information is required.');

        $this->forge->createTable('forge_test_table');
    }

    public function testCreateTableWithStringFieldException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Field information is required for that operation.');

        $this->forge->dropTable('forge_test_table', true);

        $this->forge->addField('id');
        $this->forge->addField('name');

        $this->forge->createTable('forge_test_table');
    }

    public function testRenameTable()
    {
        $this->forge->dropTable('forge_test_table_dummy', true);

        $this->forge->addField('id');
        $this->forge->addField('name varchar(100) NULL');

        $this->forge->createTable('forge_test_table');

        $this->forge->renameTable('forge_test_table', 'forge_test_table_dummy');

        $exist = $this->db->tableExists('forge_test_table_dummy');

        $this->assertTrue($exist);
    }

    public function testRenameTableEmptyNameException()
    {
        $this->forge->dropTable('forge_test_table_dummy', true);

        $this->forge->addField('id');
        $this->forge->addField('name varchar(100) NULL');

        $this->forge->createTable('forge_test_table');

        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('A table name is required for that operation.');

        $this->forge->renameTable('forge_test_table', '');
    }

    public function testRenameTableNoRenameStatementException()
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

    public function testDropTableWithEmptyName()
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('A table name is required for that operation.');

        $this->forge->dropTable('', true);
    }

    public function testForeignKey()
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

        $this->assertSame($foreignKeyData[$this->db->DBPrefix . $tableName . '_users_id_foreign']['name'], $this->db->DBPrefix . 'forge_test_invoices_users_id_foreign');
        $this->assertSame($foreignKeyData[$this->db->DBPrefix . $tableName . '_users_id_foreign']['field'], ['users_id']);
        $this->assertSame($foreignKeyData[$this->db->DBPrefix . $tableName . '_users_id_foreign']['referenceField'], ['id']);
        $this->assertSame($foreignKeyData[$this->db->DBPrefix . $tableName . '_users_id_foreign']['table'], $this->db->DBPrefix . $tableName);
        $this->assertSame($foreignKeyData[$this->db->DBPrefix . $tableName . '_users_id_foreign']['referenceTable'], $this->db->DBPrefix . 'forge_test_users');

        $this->forge->dropTable($tableName, true);
        $this->forge->dropTable('forge_test_users', true);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/4986
     */
    public function testForeignKeyAddingWithStringFields()
    {
        if ($this->db->DBDriver !== 'MySQLi') {
            $this->markTestSkipped('Testing only on MySQLi but fix expands to all DBs.');
        }

        $attributes = ['ENGINE' => 'InnoDB'];

        $this->forge->addField([
            '`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY',
            '`name` VARCHAR(255) NOT NULL',
        ])->createTable('forge_test_users', true, $attributes);

        $this->forge
            ->addField([
                '`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY',
                '`users_id` INT(11) NOT NULL',
                '`name` VARCHAR(255) NOT NULL',
            ])
            ->addForeignKey('users_id', 'forge_test_users', 'id', 'CASCADE', 'CASCADE')
            ->createTable('forge_test_invoices', true, $attributes);

        $foreignKeyData = $this->db->getForeignKeyData('forge_test_invoices');

        $this->assertSame($this->db->DBPrefix . 'forge_test_invoices_users_id_foreign', $foreignKeyData[$this->db->DBPrefix . 'forge_test_invoices_users_id_foreign']['name']);
        $this->assertSame(['users_id'], $foreignKeyData[$this->db->DBPrefix . 'forge_test_invoices_users_id_foreign']['field']);
        $this->assertSame(['id'], $foreignKeyData[$this->db->DBPrefix . 'forge_test_invoices_users_id_foreign']['referenceField']);
        $this->assertSame($this->db->DBPrefix . 'forge_test_invoices', $foreignKeyData[$this->db->DBPrefix . 'forge_test_invoices_users_id_foreign']['table']);
        $this->assertSame($this->db->DBPrefix . 'forge_test_users', $foreignKeyData[$this->db->DBPrefix . 'forge_test_invoices_users_id_foreign']['referenceTable']);

        $this->forge->dropTable('forge_test_invoices', true);
        $this->forge->dropTable('forge_test_users', true);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/4310
     */
    public function testCompositeForeignKey()
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
        $this->forge->addForeignKey(['users_id', 'users_second_id'], 'forge_test_users', ['id', 'second_id'], 'CASCADE', 'CASCADE');

        $this->forge->createTable('forge_test_invoices', true, $attributes);

        $foreignKeyData = $this->db->getForeignKeyData('forge_test_invoices');

        $haystack = ['users_id', 'users_second_id'];
        $this->assertSame($this->db->DBPrefix . 'forge_test_invoices_users_id_users_second_id_foreign', $foreignKeyData[$this->db->DBPrefix . 'forge_test_invoices_users_id_users_second_id_foreign']['name']);
        $this->assertSame($foreignKeyData[$this->db->DBPrefix . 'forge_test_invoices_users_id_users_second_id_foreign']['field'], $haystack);

        $this->assertSame($this->db->DBPrefix . 'forge_test_invoices', $foreignKeyData[$this->db->DBPrefix . 'forge_test_invoices_users_id_users_second_id_foreign']['table']);
        $this->assertSame($this->db->DBPrefix . 'forge_test_users', $foreignKeyData[$this->db->DBPrefix . 'forge_test_invoices_users_id_users_second_id_foreign']['referenceTable']);

        $this->forge->dropTable('forge_test_invoices', true);
        $this->forge->dropTable('forge_test_users', true);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/4310
     */
    public function testCompositeForeignKeyFieldNotExistException()
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Field `user_id, user_second_id` not found.');

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
        $this->forge->addForeignKey(['user_id', 'user_second_id'], 'forge_test_users', ['id', 'second_id'], 'CASCADE', 'CASCADE');

        $this->forge->createTable('forge_test_invoices', true, $attributes);
    }

    public function testForeignKeyFieldNotExistException()
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Field `user_id` not found.');

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
        $this->forge->addForeignKey('user_id', 'forge_test_users', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('forge_test_invoices', true, $attributes);
    }

    public function testDropForeignKey()
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

        $tableName      = 'forge_test_invoices';
        $foreignKeyName = 'forge_test_invoices_users_id_foreign';

        $this->forge->createTable($tableName, true, $attributes);

        $this->forge->dropForeignKey($tableName, $foreignKeyName);

        $foreignKeyData = $this->db->getForeignKeyData($tableName);

        $this->assertEmpty($foreignKeyData);

        $this->forge->dropTable($tableName, true);
        $this->forge->dropTable('forge_test_users', true);
    }

    public function testAddColumn()
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

    public function testAddFields()
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

        $fields = ['id', 'name', 'username', 'active'];
        $this->assertContains($fieldsData[0]->name, $fields);
        $this->assertContains($fieldsData[1]->name, $fields);
        unset($fields);

        if ($this->db->DBDriver === 'MySQLi') {
            $this->assertSame('int', $fieldsData[0]->type);
            $this->assertSame('varchar', $fieldsData[1]->type);

            if (version_compare($this->db->getVersion(), '8.0.17', '<')) {
                // As of MySQL 8.0.17, the display width attribute for integer data types
                // is deprecated and is not reported back anymore.
                // @see https://dev.mysql.com/doc/refman/8.0/en/numeric-type-attributes.html
                $this->assertSame(11, $fieldsData[0]->max_length);
            }

            $this->assertNull($fieldsData[0]->default);
            $this->assertNull($fieldsData[1]->default);

            $this->assertSame(1, (int) $fieldsData[0]->primary_key);

            $this->assertSame(255, (int) $fieldsData[1]->max_length);
        } elseif ($this->db->DBDriver === 'Postgre') {
            $this->assertSame('integer', $fieldsData[0]->type);
            $this->assertSame('character varying', $fieldsData[1]->type);

            $this->assertFalse($fieldsData[0]->nullable);
            $this->assertFalse($fieldsData[1]->nullable);

            $this->assertSame(32, (int) $fieldsData[0]->max_length);
            $this->assertSame(255, (int) $fieldsData[1]->max_length);

            $this->assertNull($fieldsData[1]->default);
        } elseif ($this->db->DBDriver === 'SQLite3') {
            $this->assertSame('integer', strtolower($fieldsData[0]->type));
            $this->assertSame('varchar', strtolower($fieldsData[1]->type));

            $this->assertNull($fieldsData[1]->default);
        } elseif ($this->db->DBDriver === 'SQLSRV') {
            $this->assertSame('int', $fieldsData[0]->type);
            $this->assertSame('varchar', $fieldsData[1]->type);

            $this->assertSame(10, (int) $fieldsData[0]->max_length);
            $this->assertSame(255, (int) $fieldsData[1]->max_length);

            $this->assertNull($fieldsData[1]->default);
        } elseif ($this->db->DBDriver === 'OCI8') {
            // Check types
            $this->assertSame('NUMBER', $fieldsData[0]->type);
            $this->assertSame('VARCHAR2', $fieldsData[1]->type);

            $this->assertSame('11', $fieldsData[0]->max_length);
            $this->assertSame('255', $fieldsData[1]->max_length);

            $this->assertSame('', $fieldsData[1]->default);
        } else {
            $this->fail(sprintf('DB driver "%s" is not supported.', $this->db->DBDriver));
        }
    }

    public function testCompositeKey()
    {
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

            $this->assertSame($keys['db_forge_test_1_code_company']->name, 'db_forge_test_1_code_company');
            $this->assertSame($keys['db_forge_test_1_code_company']->fields, ['code', 'company']);
            $this->assertSame($keys['db_forge_test_1_code_company']->type, 'INDEX');

            $this->assertSame($keys['db_forge_test_1_code_active']->name, 'db_forge_test_1_code_active');
            $this->assertSame($keys['db_forge_test_1_code_active']->fields, ['code', 'active']);
            $this->assertSame($keys['db_forge_test_1_code_active']->type, 'UNIQUE');
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

    public function testDropColumn()
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

    public function testModifyColumnRename()
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

    public function testConnectWithArrayGroup()
    {
        $group = config('Database');
        $group = $group->tests;

        $forge = Database::forge($group);

        $this->assertInstanceOf(Forge::class, $forge);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1983
     */
    public function testDropTableSuccess()
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

    public function testDropMultipleColumnWithArray()
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

    public function testDropMultipleColumnWithString()
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

    public function testDropKey()
    {
        $this->forge->dropTable('key_test_users', true);
        $keyName = 'key_test_users_id';

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
        $this->forge->addKey('id');
        $this->forge->createTable('key_test_users', true, $attributes);

        $this->forge->dropKey('key_test_users', $keyName);

        $foreignKeyData = $this->db->getIndexData('key_test_users');

        $this->assertEmpty($foreignKeyData);

        $this->forge->dropTable('key_test_users', true);
    }

    public function testAddTextColumnWithConstraint()
    {
        // some DBMS do not allow a constraint for type TEXT
        $result = $this->forge->addColumn('user', [
            'text_with_constraint' => ['type' => 'text', 'constraint' => 255, 'default' => ''],
        ]);

        $this->assertTrue($result);

        // SQLSRV requires dropping default constraint before dropping column
        $result = $this->forge->dropColumn('user', 'text_with_constraint');

        $this->assertTrue($result);
    }

    public function testProcessIndexes()
    {
        $this->forge->dropTable('actions', true);

        $fields = [
            'userid'      => ['type' => 'int', 'constraint' => 9],
            'category'    => ['type' => 'varchar', 'constraint' => 63],
            'name'        => ['type' => 'varchar', 'constraint' => 63],
            'uid'         => ['type' => 'varchar', 'constraint' => 63],
            'class'       => ['type' => 'varchar', 'constraint' => 63, 'null' => true],
            'input'       => ['type' => 'varchar', 'constraint' => 63, 'null' => true],
            'role'        => ['type' => 'varchar', 'constraint' => 63, 'default' => ''],
            'icon'        => ['type' => 'varchar', 'constraint' => 63, 'default' => ''],
            'summary'     => ['type' => 'varchar', 'constraint' => 255, 'default' => ''],
            'description' => ['type' => 'text', 'null' => true],
            'created_at'  => ['type' => 'datetime', 'null' => true],
            'updated_at'  => ['type' => 'datetime', 'null' => true],
            'deleted_at'  => ['type' => 'datetime', 'null' => true],
        ];

        $this->forge->addField('id');
        $this->forge->addField($fields);

        $this->forge->addKey('name');
        $this->forge->addKey('uid');
        $this->forge->addKey(['category', 'name']);
        $this->forge->addKey(['deleted_at', 'id']);
        $this->forge->addKey('created_at');

        $this->forge->addForeignKey('userid', 'user', 'id');

        $this->forge->createTable('actions');

        // now drop columns and indexes

        $this->forge->dropKey('actions', 'actions_category_name');

        $indexes = array_filter(
            $this->db->getIndexData('actions'),
            static fn ($index) => ($index->name === 'db_actions_category_name')
                    && ($index->fields === [0 => 'category', 1 => 'name'])
        );
        $this->assertCount(0, $indexes);

        $this->forge->dropForeignKey('actions', 'actions_userid_foreign');

        $this->assertCount(0, $this->db->getForeignKeyData('actions'));

        // redefine id without auto increment
        if ($this->db->DBDriver === 'MySQLi') {
            $fields = [
                'id' => [
                    'name'       => 'id',
                    'type'       => 'INT',
                    'constraint' => 9,
                ],
            ];
            $this->forge->modifyColumn('actions', $fields);
        }

        $this->forge->dropPrimaryKey('actions');

        $indexes = array_filter(
            $this->db->getIndexData('actions'),
            static fn ($index) => $index->type === 'PRIMARY'
        );
        $this->assertCount(0, $indexes);

        $this->forge->dropColumn('actions', [
            'description',
            'summary',
            'category',
            'icon',
            'role',
            'class',
        ]);

        // Add back columns and indexes
        $this->forge->addColumn('actions', [
            'class'       => ['type' => 'varchar', 'constraint' => 63, 'null' => true],
            'role'        => ['type' => 'varchar', 'constraint' => 63, 'default' => ''],
            'icon'        => ['type' => 'varchar', 'constraint' => 63, 'default' => ''],
            'category'    => ['type' => 'varchar', 'constraint' => 63, 'default' => ''],
            'summary'     => ['type' => 'varchar', 'constraint' => 255],
            'description' => ['type' => 'text', 'constraint' => 255],
        ]);

        $this->forge->addKey(['category', 'name'])->addPrimaryKey('id')->addForeignKey('userid', 'user', 'id')->processIndexes('actions');

        $indexes = array_filter(
            $this->db->getIndexData('actions'),
            static fn ($index) => ($index->name === 'db_actions_category_name')
                    && ($index->fields === [0 => 'category', 1 => 'name'])
        );
        $this->assertCount(1, $indexes);

        $this->assertCount(1, $this->db->getForeignKeyData('actions'));

        $indexes = array_filter(
            $this->db->getIndexData('actions'),
            static fn ($index) => $index->type === 'PRIMARY'
        );
        $this->assertCount(1, $indexes);

        // redefine id as auto incrememnt
        if ($this->db->DBDriver === 'MySQLi') {
            $fields = [
                'id' => [
                    'name'           => 'id',
                    'type'           => 'INT',
                    'constraint'     => 9,
                    'auto_increment' => true,
                ],
            ];
            $this->forge->modifyColumn('actions', $fields);
        }

        // test inserting data
        $data = [
            [
                'name'     => 'test name',
                'uid'      => 't',
                'category' => 'cat',
                'userid'   => 1,
            ],
            [
                'name'     => 'another name',
                'uid'      => 't',
                'category' => 'cat',
                'userid'   => 1,
            ],
        ];
        $this->db->table('actions')->insertBatch($data);

        $this->seeInDatabase('actions', [
            'id'     => 1,
            'name'   => 'test name',
            'userid' => '1',
        ]);

        $this->seeInDatabase('actions', [
            'id'     => 2,
            'name'   => 'another name',
            'userid' => '1',
        ]);

        $this->forge->dropTable('actions', true);
    }
}
