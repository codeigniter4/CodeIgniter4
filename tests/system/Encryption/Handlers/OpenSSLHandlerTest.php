<?php

namespace CodeIgniter\Encryption\Handlers;

use CodeIgniter\Encryption\Encryption;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Encryption as EncryptionConfig;

/**
 * @internal
 */
final class OpenSSLHandlerTest extends CIUnitTestCase
{
    /**
     * @var \CodeIgniter\Encryption\Encryption
     */
    protected $encryption;

    protected function setUp(): void
    {
        if (! extension_loaded('openssl')) {
            $this->markTestSkipped('OpenSSL is not available.');
        }

        $this->encryption = new Encryption();
    }

    /**
     * Sanity test
     */
    public function testSanity()
    {
        $params         = new EncryptionConfig();
        $params->driver = 'OpenSSL';
        $params->key    = 'Something other than an empty string';

        $encrypter = $this->encryption->initialize($params);

        $this->assertSame('AES-256-CTR', $encrypter->cipher);
        $this->assertSame('Something other than an empty string', $encrypter->key);
    }

    /**
     * initialize(), encrypt(), decrypt() test
     *
     * Testing the three methods separately is not realistic as they are
     * designed to work together.
     */
    public function testSimple()
    {
        $params         = new EncryptionConfig();
        $params->driver = 'OpenSSL';
        $params->key    = '\xd0\xc9\x08\xc4\xde\x52\x12\x6e\xf8\xcc\xdb\x03\xea\xa0\x3a\x5c';
        // Default state (AES-256/Rijndael-256 in CTR mode)
        $encrypter = $this->encryption->initialize($params);

        // Was the key properly set?
        $this->assertSame($params->key, $encrypter->key);

        // simple encrypt/decrypt, default parameters
        $message1 = 'This is a plain-text message.';
        $this->assertSame($message1, $encrypter->decrypt($encrypter->encrypt($message1)));
        $message2 = 'This is a different plain-text message.';
        $this->assertSame($message2, $encrypter->decrypt($encrypter->encrypt($message2)));
        $this->assertNotSame($message2, $encrypter->decrypt($encrypter->encrypt($message1)));
    }

    /**
     * Starter key needed
     */
    public function testWithoutKey()
    {
        $this->expectException('CodeIgniter\Encryption\Exceptions\EncryptionException');

        $encrypter = new OpenSSLHandler();
        $message1  = 'This is a plain-text message.';
        $encrypter->encrypt($message1, ['key' => '']);
    }

    public function testWithKeyString()
    {
        $key       = 'abracadabra';
        $encrypter = new OpenSSLHandler();
        $message1  = 'This is a plain-text message.';
        $encoded   = $encrypter->encrypt($message1, $key);
        $this->assertSame($message1, $encrypter->decrypt($encoded, $key));
    }

    /**
     * Authentication will fail decrypting with the wrong key
     */
    public function testWithWrongKeyString()
    {
        $this->expectException('CodeIgniter\Encryption\Exceptions\EncryptionException');

        $key1      = 'abracadabra';
        $encrypter = new OpenSSLHandler();
        $message1  = 'This is a plain-text message.';
        $encoded   = $encrypter->encrypt($message1, $key1);
        $this->assertNotSame($message1, $encoded);
        $key2 = 'Holy cow, batman!';
        $this->assertNotSame($message1, $encrypter->decrypt($encoded, $key2));
    }

    public function testWithKeyArray()
    {
        $key       = 'abracadabra';
        $encrypter = new OpenSSLHandler();
        $message1  = 'This is a plain-text message.';
        $encoded   = $encrypter->encrypt($message1, ['key' => $key]);
        $this->assertSame($message1, $encrypter->decrypt($encoded, ['key' => $key]));
    }

    /**
     * Authentication will fail decrypting with the wrong key
     */
    public function testWithWrongKeyArray()
    {
        $this->expectException('CodeIgniter\Encryption\Exceptions\EncryptionException');

        $key1      = 'abracadabra';
        $encrypter = new OpenSSLHandler();
        $message1  = 'This is a plain-text message.';
        $encoded   = $encrypter->encrypt($message1, ['key' => $key1]);
        $this->assertNotSame($message1, $encoded);
        $key2 = 'Holy cow, batman!';
        $this->assertNotSame($message1, $encrypter->decrypt($encoded, ['key' => $key2]));
    }
}
