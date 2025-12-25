<?php

declare(strict_types=1);

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
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Encryption\ConfigWithPreviousKeys;

/**
 * @internal
 */
#[Group('Others')]
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

    public function testHandlerCanBeReusedAfterEncryption(): void
    {
        $params         = new EncryptionConfig();
        $params->driver = 'OpenSSL';
        $params->key    = '\xd0\xc9\x08\xc4\xde\x52\x12\x6e\xf8\xcc\xdb\x03\xea\xa0\x3a\x5c';

        $encrypter = $this->encryption->initialize($params);
        $message   = 'Some message to encrypt';

        $ciphertext = $encrypter->encrypt($message);
        $plaintext  = $encrypter->decrypt($ciphertext);

        $this->assertSame($message, $plaintext);

        // Should also work for another encryption
        $message2    = 'Another message';
        $ciphertext2 = $encrypter->encrypt($message2);
        $plaintext2  = $encrypter->decrypt($ciphertext2);

        $this->assertSame($message2, $plaintext2);
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

    public function testDecryptWithPreviousKeys(): void
    {
        $config         = new ConfigWithPreviousKeys();
        $config->driver = 'OpenSSL';

        $encrypter = $this->encryption->initialize($config);

        $message = 'Secret message';

        // Encrypt with old key
        $encrypted = $encrypter->encrypt($message, 'old-key-1');

        // Decrypt without providing key - should use config key and fall back to previousKeys
        $decrypted = $encrypter->decrypt($encrypted);

        $this->assertSame($message, $decrypted);
    }

    public function testDecryptWithPreviousKeysOrder(): void
    {
        $config         = new ConfigWithPreviousKeys();
        $config->driver = 'OpenSSL';

        $encrypter = $this->encryption->initialize($config);

        $message = 'Secret message';

        // Encrypt with second old key
        $encrypted = $encrypter->encrypt($message, 'old-key-2');

        // Should successfully decrypt using second previousKey
        $decrypted = $encrypter->decrypt($encrypted);

        $this->assertSame($message, $decrypted);
    }

    public function testDecryptWithExplicitKeyDoesNotUsePreviousKeys(): void
    {
        $this->expectException(EncryptionException::class);

        $config         = new ConfigWithPreviousKeys();
        $config->driver = 'OpenSSL';

        $encrypter = $this->encryption->initialize($config);

        $message = 'Secret message';

        // Encrypt with old key
        $encrypted = $encrypter->encrypt($message, 'old-key-1');

        // Try to decrypt with explicit wrong key - should NOT fall back to previousKeys
        $encrypter->decrypt($encrypted, 'wrong-key');
    }

    public function testDecryptWithExplicitKeyArrayDoesNotUsePreviousKeys(): void
    {
        $this->expectException(EncryptionException::class);

        $config         = new ConfigWithPreviousKeys();
        $config->driver = 'OpenSSL';

        $encrypter = $this->encryption->initialize($config);

        $message = 'Secret message';

        // Encrypt with old key
        $encrypted = $encrypter->encrypt($message, 'old-key-1');

        // Try to decrypt with explicit wrong key in array - should NOT fall back to previousKeys
        $encrypter->decrypt($encrypted, ['key' => 'wrong-key']);
    }
}
