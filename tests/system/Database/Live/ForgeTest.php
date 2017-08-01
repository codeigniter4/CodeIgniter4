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
        // SQLite3 uses auto increment different
        $unique_or_auto = $this->db->DBDriver == 'SQLite3' ? 'unique' : 'auto_increment';

		$this->forge->addField([
			'id'          => [
				'type'           => 'INTEGER',
				'constraint'     => 3,
                $unique_or_auto => true,
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
}