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

		$this->assertEquals('AES-256-CBC', $this->encrypter->cipher);
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

		// Default state (AES-256/Rijndael-256 in CBC mode)
		$encrypter = $this->encryption->initialize($params);

		// Was the key properly set?
		$this->assertEquals($params['key'], $encrypter->key);

		// simple encrypt/decrypt, default parameters
		$message = 'This is a plain-text message.';
		$this->assertEquals($message, $encrypter->decrypt($encrypter->encrypt($message)));
		$message = 'This is a different plain-text message.';
		$this->assertEquals($message, $encrypter->decrypt($encrypter->encrypt($message)));
	}

	// --------------------------------------------------------------------

	/**
	 * test with different cipher
	 */
	public function testWithDES()
	{
		$params = [
			'driver' => 'OpenSSL',
			'cipher' => 'des-cbc',
			'key'	 => '\xd0\xc9\x08\xc4\xde\x52\x12\x6e\xf8\xcc\xdb\x03\xea\xa0\x3a\x5c'
		];

		$encrypter = $this->encryption->initialize($params);

		// simple encrypt/decrypt, default parameters
		$message = 'This is a plain-text message.';
		$this->assertEquals($message, $encrypter->decrypt($encrypter->encrypt($message)));
		$message = 'This is a different plain-text message.';
		$this->assertEquals($message, $encrypter->decrypt($encrypter->encrypt($message)));
	}

	/**
	 * test with & without HMAC
	 */
	public function testWithAuthentication()
	{
		$params = [
			'driver' => 'OpenSSL',
			'digest' => 'SHA512',
			'key'	 => '\xd0\xc9\x08\xc4\xde\x52\x12\x6e\xf8\xcc\xdb\x03\xea\xa0\x3a\x5c'
		];

		$encrypter = $this->encryption->initialize($params);

		// simple encrypt/decrypt, default parameters
		$message = 'This is a plain-text message.';
		$this->assertEquals($message, $encrypter->decrypt($encrypter->encrypt($message)));
		$message = 'This is a different plain-text message.';
		$this->assertEquals($message, $encrypter->decrypt($encrypter->encrypt($message)));
	}

	/**
	 * test with & without HMAC
	 */
	public function testWithoutAuthentication()
	{
		$params = [
			'driver' => 'OpenSSL',
			'digest' => '',
			'key'	 => '\xd0\xc9\x08\xc4\xde\x52\x12\x6e\xf8\xcc\xdb\x03\xea\xa0\x3a\x5c'
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
	public function testWithoutEncoding()
	{
		$params = [
			'driver'	 => 'OpenSSL',
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
			'driver'	 => 'OpenSSL',
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
			'driver'	 => 'OpenSSL',
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
