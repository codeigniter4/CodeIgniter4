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
		$this->assertNull($this->encryption->key);

		// Try with an empty value
		$config = new \Config\Encryption();
		$this->encrypt = new \CodeIgniter\Encryption\Encryption($config);
		$this->assertNull($this->encrypt->key);

		$config->key = str_repeat("\x0", 32);
		$this->encrypt = new \CodeIgniter\Encryption\Encryption($config);
		$this->assertEquals(str_repeat("\x0", 32), $this->encrypt->key);
	}

	// --------------------------------------------------------------------

	/**
	 * Ensure that the Services will give us an encrypter
	 */
	public function testService()
	{
		// Try with an empty value
		$config = new \Config\Encryption();
		$this->encrypt = \Config\Services::encrypter($config);
		$this->assertNull($this->encrypt->key);

		$config->key = str_repeat("\x0", 32);
		$this->encrypt = \Config\Services::encrypter($config);
		$this->assertEquals(str_repeat("\x0", 32), $this->encrypt->key);
	}

//	// --------------------------------------------------------------------
//	//FIXME We need more than one handler in order to include this test
//	/**
//	 * AES-256 appears to be the only common cipher. 
//	 * Let's make sure it works across all our handlers.
//	 */
//	public function testPortability()
//	{
//		$message = 'This is a message encrypted with driver and decrypted using another.';
//
//		// Format is: <Cipher name>, <Cipher mode>, <Key size>
//		$portable = [
//			['aes-256', 'cbc', 32],
//		];
//		$handlers = $this->encryption->handlers;
//
//		foreach ($portable as &$test)
//			foreach ($handlers as $encrypting)
//				foreach ($handlers as $decrypting)
//				{
//					if ($encrypting == $decrypting)
//						continue;
//
//					$params = [
//						'driver' => $encrypting,
//						'cipher' => $test[0],
//						'mode'	 => $test[1],
//						'key'	 => openssl_random_pseudo_bytes($test[2])
//					];
//
//					$encrypter = $this->encryption->initialize($params);
//					$ciphertext = $encrypter->encrypt($message);
//
//					$params['driver'] = $decrypting;
//
//					$decrypter = $this->encryption->initialize($params);
//					$this->assertEquals($message, $decrypter->decrypt($ciphertext), 'From ' . $encrypting . ' to ' . $decrypting);
//				}
//	}

}
