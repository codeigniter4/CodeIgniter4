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
            
                $this->forge->dropTable('forge_test_users_1', true);
		$this->forge->dropTable('forge_test_invoices_1', true);
                
		$this->forge->addField([
			'id'          => [
				'type'           => 'INTEGER',
				'constraint'     => 11,
			],
			'name'        => [
				'type'       => 'VARCHAR',
				'constraint' => 255,
			]
		]);
		$this->forge->addKey('id', true);
		$this->forge->createTable('forge_test_users_1', true);

                
                $this->forge->addField([
			'id'          => [
				'type'           => 'INTEGER',
				'constraint'     => 11,
			],
			'users_id'        => [
				'type'       => 'INTEGER',
				'constraint' => 11,
			],
                        'name'        => [
				'type'       => 'VARCHAR',
				'constraint' => 255,
			]
		]);
		$this->forge->addKey('id', true);
                $this->forge->addForeignKey('users_id','forge_test_users_1','id','CASCADE','CASCADE');
                
		$this->forge->createTable('forge_test_invoices_1', true);
                
                
                //Insert User example
                $insertData = [
                    'id' => 1,
                    'name' => 'John Doe'
                ];
                $this->db->table('forge_test_users_1')->insert($insertData);
                
                //Insert invoices example
                $insertData = [
                    'id' => 1,
                    'users_id' => 1,
                    'name' => 'Invoice 1'
                ];
                $invoice1 = $this->db->table('forge_test_invoices_1')->insert($insertData);
                
                $insertData = [
                    'id' => 2,
                    'users_id' => 2,
                    'name' => 'Invoice 2'
                ];
                $invoice2 = $this->db->table('forge_test_invoices_1')->insert($insertData);
                
                
                $this->assertInstanceOf('CodeIgniter\Database\BaseResult', $invoice1);
                
                $this->assertFalse($invoice2);
		

                //$this->forge->dropTable('forge_test_users_1', true);
		//$this->forge->dropTable('forge_test_invoices_1', true);
	}
}