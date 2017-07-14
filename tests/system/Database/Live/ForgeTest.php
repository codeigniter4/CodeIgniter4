<?php namespace CodeIgniter\Database\Live;

/**
 * @group DatabaseLive
 */
class ForgeTest extends \CIDatabaseTestCase
{
	protected $refresh = true;

	protected $seed = 'CITestSeeder';

	public function setUp()
	{
		parent::setUp();
		$this->forge = \Config\Database::forge($this->DBGroup);
	}

	public function testCompositeKey()
	{
		$this->forge->addField([
			'id'          => [
				'type'           => 'INTEGER',
				'constraint'     => 3,
				'auto_increment' => true,
			],
			'code'        => [
				'type'       => 'VARCHAR',
				'constraint' => 40,
			],
			'company'        => [
				'type'       => 'VARCHAR',
				'constraint' => 40,
			],
		]);
		$this->forge->addKey('id', true);
		$this->forge->addKey(['code', 'company']);
		$this->forge->createTable('forge_test_1', true);

		$keys = $this->db->getIndexData('forge_test_1');
		$this->assertEquals($keys[0]->fields, ['id']);
		$this->assertEquals($keys[1]->fields, ['code', 'company']);

		$this->forge->dropTable('forge_test_1', true);
	}
        
	public function testForeignKey()
        {
            
            $attributes = [];
            
            if ($this->db->DBDriver == 'MySQLi')
            {
                $attributes = array('ENGINE' => 'InnoDB');
            }
            
            $this->forge->addField([
                'id' => [
                    'type' => 'INTEGER',
                    'constraint' => 11,
                ],
                'name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                ]
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('forge_test_users', true, $attributes);

            $this->forge->addField([
                'id' => [
                    'type' => 'INTEGER',
                    'constraint' => 11,
                ],
                'users_id' => [
                    'type' => 'INTEGER',
                    'constraint' => 11,
                ],
                'name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                ]
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('users_id', 'forge_test_users', 'id', 'CASCADE', 'CASCADE');

            $this->forge->createTable('forge_test_invoices', true, $attributes);
            
            $foreignKeyData = $this->db->getForeignKeyData('forge_test_invoices');
            
            $this->assertEquals($foreignKeyData[0]->constraint_name, $this->db->DBPrefix.'forge_test_invoices_users_id_foreign');
            $this->assertEquals($foreignKeyData[0]->table_name, $this->db->DBPrefix.'forge_test_invoices');
            $this->assertEquals($foreignKeyData[0]->foreign_table_name, $this->db->DBPrefix.'forge_test_users');
            
            $this->forge->dropTable('forge_test_invoices', true);       
            $this->forge->dropTable('forge_test_users', true);
            
        }
        
        public function testDropForeignKey()
        {
            
            $attributes = [];
            
            if ($this->db->DBDriver == 'MySQLi')
            {
                $attributes = array('ENGINE' => 'InnoDB');
            }
            
            $this->forge->addField([
                'id' => [
                    'type' => 'INTEGER',
                    'constraint' => 11,
                ],
                'name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                ]
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('forge_test_users', true, $attributes);

            $this->forge->addField([
                'id' => [
                    'type' => 'INTEGER',
                    'constraint' => 11,
                ],
                'users_id' => [
                    'type' => 'INTEGER',
                    'constraint' => 11,
                ],
                'name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                ]
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('users_id', 'forge_test_users', 'id', 'CASCADE', 'CASCADE');

            $this->forge->createTable('forge_test_invoices', true, $attributes);
            
            $this->forge->dropForeignKey('forge_test_invoices', 'forge_test_invoices_users_id_foreign');
            
            $foreignKeyData = $this->db->getForeignKeyData('forge_test_invoices');
            
            $this->assertEmpty($foreignKeyData);
            
            $this->forge->dropTable('forge_test_invoices', true);       
            $this->forge->dropTable('forge_test_users', true);
            
        }
}