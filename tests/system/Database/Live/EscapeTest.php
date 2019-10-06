<?php namespace CodeIgniter\Database\Live;

use CodeIgniter\Test\CIDatabaseTestCase;

/**
 * @group DatabaseLive
 */
class EscapeTest extends CIDatabaseTestCase
{
	protected $refresh = false;

	protected $char;

	protected function setUp(): void
	{
		parent::setUp();

		$this->char = $this->db->DBDriver === 'MySQLi' ? '\\' : "'";
	}

	//--------------------------------------------------------------------

	/**
	 * Ensures we don't have escaped - values...
	 *
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/606
	 */
	public function testEscapeProtectsNegativeNumbers()
	{
		$this->assertEquals("'-100'", $this->db->escape(-100));
	}

	//--------------------------------------------------------------------

	public function testEscape()
	{
		$expected = "SELECT * FROM brands WHERE name = 'O" . $this->char . "'Doules'";
		$sql      = "SELECT * FROM brands WHERE name = " . $this->db->escape("O'Doules");

		$this->assertEquals($expected, $sql);
	}

	//--------------------------------------------------------------------

	public function testEscapeString()
	{
		$expected = "SELECT * FROM brands WHERE name = 'O" . $this->char . "'Doules'";
		$sql      = "SELECT * FROM brands WHERE name = '" . $this->db->escapeString("O'Doules") . "'";

		$this->assertEquals($expected, $sql);
	}

	//--------------------------------------------------------------------

	public function testEscapeLikeString()
	{
		$expected = "SELECT * FROM brands WHERE column LIKE '%10!% more%' ESCAPE '!'";
		$sql      = "SELECT * FROM brands WHERE column LIKE '%" . $this->db->escapeLikeString("10% more") . "%' ESCAPE '!'";

		$this->assertEquals($expected, $sql);
	}

	//--------------------------------------------------------------------

	public function testEscapeLikeStringDirect()
	{
		if ($this->db->DBDriver === 'MySQLi')
		{
			$expected = "SHOW COLUMNS FROM brands WHERE column LIKE 'wild\_chars%'";
			$sql      = "SHOW COLUMNS FROM brands WHERE column LIKE '". $this->db->escapeLikeStringDirect("wild_chars") . "%'";

			$this->assertEquals($expected, $sql);
		}
		else
		{
			$this->expectNotToPerformAssertions();
		}
	}
}
