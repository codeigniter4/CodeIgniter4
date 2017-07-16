<?php namespace CodeIgniter\Encryption;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Encryption\MockEncryption;

class EncryptionTest extends CIUnitTestCase
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
		$config = new \Config\Encryption();
		$this->encrypt = new \CodeIgniter\Encryption\Encryption($config);
		$this->assertEmpty($this->encrypt->key);

		// try a different key
		$ikm = str_repeat("\x0", 32);
		$ikm = "Secret stuff";
		$config->key = $ikm;
		$this->encrypt = new \CodeIgniter\Encryption\Encryption($config);
		$this->assertEquals($ikm, $this->encrypt->key);
	}

	/**
	 * Covers behavior with invalid parameters
	 * 
	 * @expectedException \CodeIgniter\Encryption\EncryptionException
	 */
	public function testBadDriver()
	{
		// ask for a bad driver
		$config = new \Config\Encryption();
		$config->driver = 'Bogus';
		$this->encrypt = new \CodeIgniter\Encryption\Encryption($config);
		$this->encrypt->initialize();
		$this->assertNotNull($this->encrypt);
	}

	/**
	 * Covers behavior with invalid parameters
	 * 
	 * @expectedException \CodeIgniter\Encryption\EncryptionException
	 */
	public function testBadDigest()
	{
		// ask for a bad digest
		$config = new \Config\Encryption();
		$config->digest = 'Bogus';
		$this->encrypt = new \CodeIgniter\Encryption\Encryption($config);
		$this->encrypt->initialize();
		$this->assertNotNull($this->encrypt);
	}

	// --------------------------------------------------------------------

	/**
	 * Config parameters test
	 */
	public function testParameters()
	{
		// make sure we don't actually need parameters
		$this->assertTrue(is_array($this->encryption->config));

		// check that defaults are there
		$defaults = $this->encryption->default;
		foreach ($defaults as $key => $value)
			$this->assertEquals($value, $this->encryption->$key);

		// make sure we can over-ride any parameter
		// change the driver once we have more than 1
		$expected = [
			'driver'	 => 'OpenSSL', // The PHP extension we plan to use
			'key'		 => 'Top banana', // no starting key material
			'cipher'	 => 'AES-128-CBC', // Encryption cipher
			'digest'	 => '', // HMAC digest algorithm to use
			'encoding'	 => '', // Base64 encoding?
		];
		$this->encrypt = new \CodeIgniter\Encryption\Encryption($expected);
		foreach ($expected as $key => $value)
			$this->assertEquals($value, $this->encrypt->$key);
	}

	// --------------------------------------------------------------------

	public function testKeyCreation()
	{
		$this->assertNotEmpty($this->encryption->createKey());
		$this->assertEquals(32, strlen($this->encryption->createKey()));
		$this->assertEquals(16, strlen($this->encryption->createKey(16)));
	}

	// --------------------------------------------------------------------

	/**
	 * Initialization test
	 */
	public function testInitialization()
	{
		// make sure we can over-ride any parameter
		// change the driver once we have more than 1
		$expected = [
			'driver'	 => 'OpenSSL', // The PHP extension we plan to use
			'key'		 => 'Top banana', // no starting key material
			'cipher'	 => 'AES-256-CBC', // Encryption cipher
			'digest'	 => 'SHA512', // HMAC digest algorithm to use
			'encoding'	 => 'base64', // Base64 encoding?
		];
		$this->encrypt = $this->encryption->initialize($expected);
		foreach ($expected as $key => $value)
			$this->assertEquals($value, $this->encrypt->$key);
	}

}
