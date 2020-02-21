<?php
namespace CodeIgniter\Encryption;

use Config\Services;
use CodeIgniter\Config\BaseConfig;
use Config\Encryption as EncryptionConfig;

//use CodeIgniter\Encryption\Encryption;

class EncryptionTest extends \CodeIgniter\Test\CIUnitTestCase
{

	public function setUp(): void
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
		$config        = new EncryptionConfig();
		$this->encrypt = new \CodeIgniter\Encryption\Encryption($config);
		$this->assertEmpty($this->encrypt->key);

		// try a different key
		$ikm           = 'Secret stuff';
		$config->key   = $ikm;
		$this->encrypt = new \CodeIgniter\Encryption\Encryption($config);
		$this->assertEquals($ikm, $this->encrypt->key);
	}

	/**
	 * Covers behavior with invalid parameters
	 */
	public function testBadDriver()
	{
		$this->expectException('CodeIgniter\Encryption\Exceptions\EncryptionException');

		// ask for a bad driver
		$config         = new EncryptionConfig();
		$config->driver = 'Bogus';
		$config->key    = 'anything';

		$encrypter = $this->encryption->initialize($config);
	}

	/**
	 * Covers behavior with invalid parameters
	 */
	public function testMissingDriver()
	{
		$this->expectException('CodeIgniter\Encryption\Exceptions\EncryptionException');

		// ask for a bad driver
		$config         = new EncryptionConfig();
		$config->driver = '';
		$config->key    = 'anything';

		$encrypter = $this->encryption->initialize($config);
	}

	// --------------------------------------------------------------------

	public function testKeyCreation()
	{
		$this->assertNotEmpty($this->encryption->createKey());
		$this->assertEquals(32, strlen($this->encryption->createKey()));
		$this->assertEquals(16, strlen($this->encryption->createKey(16)));
	}

	// --------------------------------------------------------------------

	public function testServiceSuccess()
	{
		$config         = new EncryptionConfig();
		$config->driver = 'OpenSSL';
		$config->key    = 'anything';

		$encrypter = Services::encrypter($config);
		$this->assertInstanceOf(EncrypterInterface::class, $encrypter);
	}

	public function testServiceFailure()
	{
		$this->expectException('CodeIgniter\Encryption\Exceptions\EncryptionException');

		// ask for a bad driver
		$config         = new EncryptionConfig();
		$config->driver = 'Kazoo';
		$config->key    = 'anything';

		$encrypter = Services::encrypter($config);
	}

	public function testServiceWithoutKey()
	{
		$this->expectException('CodeIgniter\Encryption\Exceptions\EncryptionException');

		$encrypter = Services::encrypter();
	}

	public function testServiceShared()
	{
		$config         = new EncryptionConfig();
		$config->driver = 'OpenSSL';
		$config->key    = 'anything';

		$encrypter = Services::encrypter($config, true);

		$config->key = 'Abracadabra';
		$encrypter   = Services::encrypter($config, true);
		$this->assertEquals('anything', $encrypter->key);
	}

	//--------------------------------------------------------------------

	public function testMagicIssetTrue()
	{
		$this->assertTrue(isset($this->encryption->digest));
	}

	public function testMagicIssetFalse()
	{
		$this->assertFalse(isset($this->encryption->bogus));
	}

	public function testMagicGet()
	{
		$this->assertEquals('SHA512', $this->encryption->digest);
	}

	public function testMagicGetMissing()
	{
		$this->assertNull($this->encryption->bogus);
	}

}
