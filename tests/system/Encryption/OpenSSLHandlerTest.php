<?php
namespace CodeIgniter\Encrypt;

use CodeIgniter\Config\Services;
use CodeIgniter\Test\CIUnitTestCase;

class OpenSSLHandlerTest extends CIUnitTestCase
{

	public function setUp(): void
	{
		$this->encryption = new \CodeIgniter\Encryption\Encryption();
	}

	// --------------------------------------------------------------------

	/**
	 * Sanity test
	 */
	public function testSanity()
	{
		$params         = new \Config\Encryption();
		$params->driver = 'OpenSSL';
		$params->key    = 'Something other than an empty string';

		$this->encrypter = $this->encryption->initialize($params);

		$this->assertEquals('AES-256-CTR', $this->encrypter->cipher);
		$this->assertEquals('Something other than an empty string', $this->encrypter->key);
	}

	// --------------------------------------------------------------------

	/**
	 * initialize(), encrypt(), decrypt() test
	 *
	 * Testing the three methods separately is not realistic as they are
	 * designed to work together.
	 */
	public function testSimple()
	{
		$params         = new \Config\Encryption();
		$params->driver = 'OpenSSL';
		$params->key    = '\xd0\xc9\x08\xc4\xde\x52\x12\x6e\xf8\xcc\xdb\x03\xea\xa0\x3a\x5c';
		// Default state (AES-256/Rijndael-256 in CTR mode)
		$encrypter = $this->encryption->initialize($params);

		// Was the key properly set?
		$this->assertEquals($params->key, $encrypter->key);

		// simple encrypt/decrypt, default parameters
		$message1 = 'This is a plain-text message.';
		$this->assertEquals($message1, $encrypter->decrypt($encrypter->encrypt($message1)));
		$message2 = 'This is a different plain-text message.';
		$this->assertEquals($message2, $encrypter->decrypt($encrypter->encrypt($message2)));
		$this->assertNotEquals($message2, $encrypter->decrypt($encrypter->encrypt($message1)));
	}

	/**
	 * Starter key needed
	 *
	 * @expectedException \CodeIgniter\Encryption\Exceptions\EncryptionException
	 */
	public function testWithoutKey()
	{
		$encrypter = new \CodeIgniter\Encryption\Handlers\OpenSSLHandler();
		$message1  = 'This is a plain-text message.';
		$encrypter->encrypt($message1);
	}

	public function testWithKeyString()
	{
		$key       = 'abracadabra';
		$encrypter = new \CodeIgniter\Encryption\Handlers\OpenSSLHandler();
		$message1  = 'This is a plain-text message.';
		$encoded   = $encrypter->encrypt($message1, $key);
		$this->assertEquals($message1, $encrypter->decrypt($encoded, $key));
	}

	/**
	 * Authentication will fail decrypting with the wrong key
	 *
	 * @expectedException \CodeIgniter\Encryption\Exceptions\EncryptionException
	 */
	public function testWithWrongKeyString()
	{
		$key1      = 'abracadabra';
		$encrypter = new \CodeIgniter\Encryption\Handlers\OpenSSLHandler();
		$message1  = 'This is a plain-text message.';
		$encoded   = $encrypter->encrypt($message1, $key1);
		$this->assertNotEquals($message1, $encoded);
		$key2 = 'Holy cow, batman!';
		$this->assertNotEquals($message1, $encrypter->decrypt($encoded, $key2));
	}

	public function testWithKeyArray()
	{
		$key       = 'abracadabra';
		$encrypter = new \CodeIgniter\Encryption\Handlers\OpenSSLHandler();
		$message1  = 'This is a plain-text message.';
		$encoded   = $encrypter->encrypt($message1, ['key' => $key]);
		$this->assertEquals($message1, $encrypter->decrypt($encoded, ['key' => $key]));
	}

	/**
	 * Authentication will fail decrypting with the wrong key
	 *
	 * @expectedException \CodeIgniter\Encryption\Exceptions\EncryptionException
	 */
	public function testWithWrongKeyArray()
	{
		$key1      = 'abracadabra';
		$encrypter = new \CodeIgniter\Encryption\Handlers\OpenSSLHandler();
		$message1  = 'This is a plain-text message.';
		$encoded   = $encrypter->encrypt($message1, ['key' => $key1]);
		$this->assertNotEquals($message1, $encoded);
		$key2 = 'Holy cow, batman!';
		$this->assertNotEquals($message1, $encrypter->decrypt($encoded, ['key' => $key2]));
	}

}
