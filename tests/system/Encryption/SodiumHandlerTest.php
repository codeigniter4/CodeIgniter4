<?php namespace CodeIgniter\Encryption;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Encryption\MockEncryption;

class SodiumHandlerTest extends CIUnitTestCase
{

	public function setUp()
	{
		if ( ! extension_loaded('libsodium'))
			$this->markTestSkipped('libsodium extension not available.');
		$this->encryption = new \CodeIgniter\Encryption\Encryption();
		$this->encrypter = $this->encryption->initialize(['driver' => 'Sodium', 'key' => 'Something other than an empty string']);
	}

	// --------------------------------------------------------------------

	/**
	 * Sanity test
	 */
	public function testSanity()
	{
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
			'driver' => 'Sodium',
			'key'	 => '\xd0\xc9\x08\xc4\xde\x52\x12\x6e\xf8\xcc\xdb\x03\xea\xa0\x3a\x5c'
		];

		// Default state 
		$encrypter = $this->encryption->initialize($params);

		// Was the key properly set?
		$this->assertEquals($params['key'], $encrypter->key);

		// simple encrypt/decrypt, default parameters
		$message = 'This is a plain-text message.';
		$this->assertEquals($message, $encrypter->decrypt($encrypter->encrypt($message)));
		$message = 'This is a different plain-text message.';
		$this->assertEquals($message, $encrypter->decrypt($encrypter->encrypt($message)));
	}

	/**
	 * test with & without encoding
	 */
	public function testWithoutEncoding()
	{
		$params = [
			'driver'	 => 'Sodium',
			'encoding'	 => '',
			'key'		 => '\xd0\xc9\x08\xc4\xde\x52\x12\x6e\xf8\xcc\xdb\x03\xea\xa0\x3a\x5c'
		];

		$encrypter = $this->encryption->initialize($params);

		// simple encrypt/decrypt, default parameters
		$message = 'This is a plain-text message.';
		$this->assertEquals($message, $encrypter->decrypt($encrypter->encrypt($message)));
		$message = 'This is a different plain-text message.';
		$this->assertEquals($message, $encrypter->decrypt($encrypter->encrypt($message)));
	}

	/**
	 * test with & without encoding
	 */
	public function testWithEncoding()
	{
		$params = [
			'driver'	 => 'Sodium',
			'encoding'	 => 'base64',
			'key'		 => '\xd0\xc9\x08\xc4\xde\x52\x12\x6e\xf8\xcc\xdb\x03\xea\xa0\x3a\x5c'
		];

		$encrypter = $this->encryption->initialize($params);

		// simple encrypt/decrypt, default parameters
		$message = 'This is a plain-text message.';
		$this->assertEquals($message, $encrypter->decrypt($encrypter->encrypt($message)));
		$message = 'This is a different plain-text message.';
		$this->assertEquals($message, $encrypter->decrypt($encrypter->encrypt($message)));
	}

	/**
	 * test with & without encoding
	 */
	public function testWithHexEncoding()
	{
		$params = [
			'driver'	 => 'Sodium',
			'encoding'	 => 'hex',
			'key'		 => '\xd0\xc9\x08\xc4\xde\x52\x12\x6e\xf8\xcc\xdb\x03\xea\xa0\x3a\x5c'
		];

		$encrypter = $this->encryption->initialize($params);

		// simple encrypt/decrypt, default parameters
		$message = 'This is a plain-text message.';
		$this->assertEquals($message, $encrypter->decrypt($encrypter->encrypt($message)));
		$message = 'This is a different plain-text message.';
		$this->assertEquals($message, $encrypter->decrypt($encrypter->encrypt($message)));
	}

}
