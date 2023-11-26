<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Encryption\Handlers;

use CodeIgniter\Encryption\Encryption;
use CodeIgniter\Encryption\Exceptions\EncryptionException;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Encryption as EncryptionConfig;

/**
 * @internal
 *
 * @group Others
 */
final class OpenSSLHandlerTest extends CIUnitTestCase
{
    private Encryption $encryption;

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
    public function testSanity(): void
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
    public function testSimple(): void
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
    public function testWithoutKey(): void
    {
        $this->expectException(EncryptionException::class);

        $encrypter = new OpenSSLHandler();
        $message1  = 'This is a plain-text message.';
        $encrypter->encrypt($message1, ['key' => '']);
    }

    public function testWithKeyString(): void
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
    public function testWithWrongKeyString(): void
    {
        $this->expectException(EncryptionException::class);

        $key1      = 'abracadabra';
        $encrypter = new OpenSSLHandler();
        $message1  = 'This is a plain-text message.';
        $encoded   = $encrypter->encrypt($message1, $key1);
        $this->assertNotSame($message1, $encoded);
        $key2 = 'Holy cow, batman!';
        $this->assertNotSame($message1, $encrypter->decrypt($encoded, $key2));
    }

    public function testWithKeyArray(): void
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
    public function testWithWrongKeyArray(): void
    {
        $this->expectException(EncryptionException::class);

        $key1      = 'abracadabra';
        $encrypter = new OpenSSLHandler();
        $message1  = 'This is a plain-text message.';
        $encoded   = $encrypter->encrypt($message1, ['key' => $key1]);
        $this->assertNotSame($message1, $encoded);
        $key2 = 'Holy cow, batman!';
        $this->assertNotSame($message1, $encrypter->decrypt($encoded, ['key' => $key2]));
    }
}
