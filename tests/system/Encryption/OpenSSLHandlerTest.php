<?php namespace CodeIgniter\Encryption;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Encryption\MockEncryption;

class OpenSSLHandlerTest extends CIUnitTestCase
{

	public function setUp()
	{
		$this->encryption = new \CodeIgniter\Encryption\Encryption();
		$this->encrypter = $this->encryption->initialize(['driver' => 'OpenSSL','key'=>'Something other than an empty string']);
	}

	// --------------------------------------------------------------------

	/**
	 * Sanity test
	 */
	public function testSanity() {
		$this->assertEquals('aes-256-cbc',$this->encrypter->cipher);
		$this->assertEquals('Something other than an empty string',$this->encrypter->key);
		$this->assertNull($this->encrypter->handle);
	}
	
	// --------------------------------------------------------------------

	/**
	 * Config parameters test
	 */
	public function testGetParams()
	{
		// make sure we don't actually need parameters
		$this->assertTrue(is_array($this->encrypter->getParams([])));

		$key = str_repeat("\x0", 32);

		// Invalid custom parameters
		$params = [
			// No cipher, mode or key
			['cipher' => 'aes-256', 'mode' => 'cbc'],
			['cipher' => 'aes-256', 'key' => $key],
			['mode' => 'cbc', 'key' => $key],
			// No HMAC key or not a valid digest
			['cipher' => 'aes-256', 'mode' => 'cbc', 'key' => $key],
			['cipher' => 'aes-256', 'mode' => 'cbc', 'key' => $key, 'hmac_digest' => 'sha1', 'hmac_key' => $key],
			// Invalid mode
			['cipher' => 'aes-256', 'mode' => 'foo', 'key' => $key, 'hmac_digest' => 'sha256', 'hmac_key' => $key]
		];

		for ($i = 0, $c = count($params); $i < $c; $i ++ )
		{
			$this->assertFalse($this->encrypter->getParams($params[$i]));
		}

		// Valid parameters
		$params = [
			'cipher'	 => 'aes-256',
			'mode'		 => 'cbc',
			'key'		 => str_repeat("\x0", 32),
			'hmac_key'	 => str_repeat("\x0", 32)
		];

		$this->assertTrue(is_array($this->encrypter->getParams($params)));

		// why do we have the next 2 lines?
		$params['base64'] = TRUE;
		$params['hmac_digest'] = 'sha512';

		// Including all parameters
		$params = [
			'cipher'		 => 'aes-256',
			'mode'			 => 'cbc',
			'key'			 => str_repeat("\x0", 32),
			'raw_data'		 => TRUE,
			'hmac_key'		 => str_repeat("\x0", 32),
			'hmac_digest'	 => 'sha256'
		];

		$output = $this->encrypter->getParams($params);
		unset($output['handle'], $output['cipher'], $params['raw_data'], $params['cipher']);
		$params['base64'] = FALSE;
		$this->assertEquals($params, $output);

		// HMAC disabled
		unset($params['hmac_key'], $params['hmac_digest']);
		$params['hmac'] = $params['raw_data'] = FALSE;
		$params['cipher'] = 'aes-256';
		$output = $this->encrypter->getParams($params);
		unset($output['handle'], $output['cipher'], $params['hmac'], $params['raw_data'], $params['cipher']);
		$params['base64'] = TRUE;
		$params['hmac_digest'] = $params['hmac_key'] = null;
		$this->assertEquals($params, $output);
	}

	// --------------------------------------------------------------------

	/**
	 * initialize(), encrypt(), decrypt() test
	 *
	 * Testing the three methods separately is not realistic as they are
	 * designed to work together. 
	 */
	public function testInitializeEncryptDecrypt()
	{
		$message = 'This is a plain-text message.';
		$key = "\xd0\xc9\x08\xc4\xde\x52\x12\x6e\xf8\xcc\xdb\x03\xea\xa0\x3a\x5c";
		$key = $key . $key;

		// Default state (AES-256/Rijndael-256 in CBC mode)
		$encrypter = $this->encryption->initialize(array('key' => $key));

		// Was the key properly set?
		$this->assertEquals($key, $encrypter->key);

		$this->assertEquals($message, $encrypter->decrypt($encrypter->encrypt($message)));

		// Try DES in ECB mode, just for the sake of changing stuff
		$encrypter = $this->encryption->initialize(array('cipher' => 'des', 'mode' => 'ecb', 'key' => substr($key, 0, 8)));
		$this->assertEquals($message, $encrypter->decrypt($encrypter->encrypt($message)));
	}

	// --------------------------------------------------------------------

	/**
	 * encrypt(), decrypt test with custom parameters
	 */
	public function testEncryptDecryptCustom()
	{
		$message = 'Another plain-text message.';

		$encrypter = $this->encryption->initialize();

		// A random invalid parameter
		$this->assertFalse($encrypter->encrypt($message, array('foo')));
		$this->assertFalse($encrypter->decrypt($message, array('foo')));

		// No HMAC, binary output
		$params = [
			'cipher' => 'tripledes',
			'mode'	 => 'cfb',
			'key'	 => str_repeat("\x1", 16),
			'base64' => FALSE,
			'hmac'	 => FALSE
		];

		$ciphertext = $encrypter->encrypt($message, $params);

		$this->assertEquals($message, $encrypter->decrypt($ciphertext, $params));
	}

}
