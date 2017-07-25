<?php namespace CodeIgniter\Encryption;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Encryption\MockEncryption;

class OpenSSLHandlerTest extends CIUnitTestCase
{

	public function setUp()
	{
		$this->encryption = new \CodeIgniter\Encryption\Encryption();
	}

	// --------------------------------------------------------------------

	/**
	 * Sanity test
	 */
	public function testSanity()
	{
		$params = [
			'driver' => 'OpenSSL',
			'key'	 => 'Something other than an empty string'
		];
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
		$params = [
			'driver' => 'OpenSSL',
			'key'	 => '\xd0\xc9\x08\xc4\xde\x52\x12\x6e\xf8\xcc\xdb\x03\xea\xa0\x3a\x5c'
		];

		// Default state (AES-256/Rijndael-256 in CTR mode)
		$encrypter = $this->encryption->initialize($params);

		// Was the key properly set?
		$this->assertEquals($params['key'], $encrypter->key);

		// simple encrypt/decrypt, default parameters
		$message = 'This is a plain-text message.';
		$this->assertEquals($message, $encrypter->decrypt($encrypter->encrypt($message)));
		$message = 'This is a different plain-text message.';
		$this->assertEquals($message, $encrypter->decrypt($encrypter->encrypt($message)));
	}

}
