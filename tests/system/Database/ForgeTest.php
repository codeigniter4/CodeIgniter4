<?php namespace CodeIgniter\Database;

use CodeIgniter\Database\MockConnection;

class ForgeTest extends \CIUnitTestCase
{
	protected $db;
	//--------------------------------------------------------------------
	public function setUp()
	{
		$this->db = new MockConnection([]);
	}
	//--------------------------------------------------------------------
	public function testAddForeignKey()
	{
	    $forge = new Forge($this->db);
	    
	    $fk[] = [
	        'key' => 'key',
	        'fkey' => 'fkey',
	        'table' => 'table',
	        'ftable' => 'ftable',
	        'onUpdate' => 'CASCADE',
	        'onDelete' => 'CASCADE'
	    ];
	    
	    $forge->addForeignKey('key', 'fkey', 'table', 'ftable', 'CASCADE', 'CASCADE');
		$this->assertEquals($fk, $forge->getForeingKeys());
	}
	
	//-------------------------------------------------------------------
	
	public function testCreateForeignKeys()
	{
	    $forge = new Forge($this->db);
	    
	    $fk = [
	            'key' => 'key',
	            'fkey' => 'fkey',
	            'table' => 'table',
	            'ftable' => 'ftable',
	            'onUpdate' => 'CASCADE',
	            'onDelete' => 'CASCADE'
	    ];
	    
	    $forge->addForeignKey('key', 'fkey', 'table', 'ftable', 'CASCADE', 'CASCADE');
	     
	    $sql = 'ALTER TABLE table ADD CONSTRAINT fk_ftable_fkey FOREIGN KEY (key) REFERENCES ftable (fkey) ON UPDATE CASCADE ON DELETE CASCADE';
	
	    $this->assertEquals($sql, $forge->createForeignKeys($fk));
	}
	
	//-------------------------------------------------------------------
	
	public function testDeleteForeignKey()
	{
	    $forge = new Forge($this->db);
	    
	    $sql = 'ALTER TABLE table DROP FOREIGN KEY fk_fkey';
	
	    $this->assertEquals($sql, $forge->deleteForeignKey('table', 'fk_fkey'));
	}
}
