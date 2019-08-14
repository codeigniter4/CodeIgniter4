<?php
namespace CodeIgniter\Encryption;

use Config\Services;
use CodeIgniter\Config\BaseConfig;

//use CodeIgniter\Encryption\Encryption;

class EncryptionTest extends \CIUnitTestCase
{

	public function setUp()
	{
		$this->encryption = new \CodeIgniter\Encryption\Encryption();
	}

	// --------------------------------------------------------------------

	/**
	 * __construct test
	 *
	 * Covers behavior with config encryption key set or not
	 */
	public function testConstructor()
	{
		// Assume no configuration from set_up()
		$this->assertEmpty($this->encryption->key);

		// Try with an empty value
		$config        = new \Config\Encryption();
		$this->encrypt = new \CodeIgniter\Encryption\Encryption($config);
		$this->assertEmpty($this->encrypt->key);

		// try a different key
		$ikm           = str_repeat("\x0", 32);
		$ikm           = 'Secret stuff';
		$config->key   = $ikm;
		$this->encrypt = new \CodeIgniter\Encryption\Encryption($config);
		$this->assertEquals($ikm, $this->encrypt->key);
	}

	/**
	 * Covers behavior with invalid parameters
	 *
	 * @expectedException \CodeIgniter\Encryption\Exceptions\EncryptionException
	 */
	public function testBadDriver()
	{
		// ask for a bad driver
		$config         = new \Config\Encryption();
		$config->driver = 'Bogus';
		$this->encrypt  = new \CodeIgniter\Encryption\Encryption($config);
		$this->encrypt->initialize();
		$this->assertNotNull($this->encrypt);
	}

	// --------------------------------------------------------------------

	public function testKeyCreation()
	{
		$this->assertNotEmpty($this->encryption->createKey());
		$this->assertEquals(32, strlen($this->encryption->createKey()));
		$this->assertEquals(16, strlen($this->encryption->createKey(16)));
	}

}
