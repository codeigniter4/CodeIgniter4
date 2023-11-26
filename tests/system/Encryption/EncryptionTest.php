<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Encryption;

use CodeIgniter\Encryption\Exceptions\EncryptionException;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Encryption as EncryptionConfig;
use Config\Services;

/**
 * @internal
 *
 * @group Others
 */
final class EncryptionTest extends CIUnitTestCase
{
    private Encryption $encryption;

    protected function setUp(): void
    {
        $this->encryption = new Encryption();
    }

    /**
     * __construct test
     *
     * Covers behavior with config encryption key set or not
     */
    public function testConstructor(): void
    {
        // Assume no configuration from set_up()
        $this->assertEmpty($this->encryption->key);

        // Try with an empty value
        $config           = new EncryptionConfig();
        $this->encryption = new Encryption($config);
        $this->assertEmpty($this->encryption->key);

        // try a different key
        $ikm              = 'Secret stuff';
        $config->key      = $ikm;
        $this->encryption = new Encryption($config);
        $this->assertSame($ikm, $this->encryption->key);
    }

    /**
     * Covers behavior with invalid parameters
     */
    public function testBadDriver(): void
    {
        $this->expectException(EncryptionException::class);

        // ask for a bad driver
        $config         = new EncryptionConfig();
        $config->driver = 'Bogus';
        $config->key    = 'anything';

        $this->encryption->initialize($config);
    }

    /**
     * Covers behavior with invalid parameters
     */
    public function testMissingDriver(): void
    {
        $this->expectException(EncryptionException::class);

        // ask for a bad driver
        $config         = new EncryptionConfig();
        $config->driver = '';
        $config->key    = 'anything';

        $this->encryption->initialize($config);
    }

    public function testKeyCreation(): void
    {
        $this->assertNotEmpty($this->encryption->createKey());
        $this->assertSame(32, strlen($this->encryption->createKey()));
        $this->assertSame(16, strlen($this->encryption->createKey(16)));
    }

    public function testServiceSuccess(): void
    {
        $config         = new EncryptionConfig();
        $config->driver = 'OpenSSL';
        $config->key    = 'anything';

        $encrypter = Services::encrypter($config);
        $this->assertInstanceOf(EncrypterInterface::class, $encrypter);
    }

    public function testServiceFailure(): void
    {
        $this->expectException(EncryptionException::class);

        // ask for a bad driver
        $config         = new EncryptionConfig();
        $config->driver = 'Kazoo';
        $config->key    = 'anything';

        Services::encrypter($config);
    }

    public function testServiceWithoutKey(): void
    {
        $this->expectException(EncryptionException::class);

        Services::encrypter();
    }

    public function testServiceShared(): void
    {
        $config         = new EncryptionConfig();
        $config->driver = 'OpenSSL';
        $config->key    = 'anything';

        $encrypter = Services::encrypter($config, true);

        $config->key = 'Abracadabra';
        $encrypter   = Services::encrypter($config, true);
        $this->assertSame('anything', $encrypter->key);
    }

    public function testMagicIssetTrue(): void
    {
        $this->assertTrue(isset($this->encryption->digest));
    }

    public function testMagicIssetFalse(): void
    {
        $this->assertFalse(isset($this->encryption->bogus));
    }

    public function testMagicGet(): void
    {
        $this->assertSame('SHA512', $this->encryption->digest);
    }

    public function testMagicGetMissing(): void
    {
        $this->assertNull($this->encryption->bogus);
    }

    public function testDecryptEncryptedDataByCI3AES128CBC(): void
    {
        $config                 = new EncryptionConfig();
        $config->driver         = 'OpenSSL';
        $config->key            = hex2bin('64c70b0b8d45b80b9eba60b8b3c8a34d0193223d20fea46f8644b848bf7ce67f');
        $config->cipher         = 'AES-128-CBC'; // CI3's default config
        $config->rawData        = false;
        $config->encryptKeyInfo = 'encryption';
        $config->authKeyInfo    = 'authentication';
        $encrypter              = Services::encrypter($config, false);

        $encrypted = '211c55b9d1948187557bff88c1e77e0f6b965e3711d477d97fb0b60907a7336028714dbb8dfe90598039e9bc7147b54e552d739b378cd864fb91dde9ad6d4ffalIvVxFDDLTPBYGaHLNDzUSJExBKbQJ0NW27KDaR83bYqz8MDz/mXXpE+HHdaWjEE';
        $decrypted = $encrypter->decrypt($encrypted);

        $expected = 'This is a plain-text message.';
        $this->assertSame($expected, $decrypted);
    }

    public function testDecryptEncryptedDataByCI3AES256CTR(): void
    {
        $config                 = new EncryptionConfig();
        $config->driver         = 'OpenSSL';
        $config->key            = hex2bin('64c70b0b8d45b80b9eba60b8b3c8a34d0193223d20fea46f8644b848bf7ce67f');
        $config->rawData        = false;
        $config->encryptKeyInfo = 'encryption';
        $config->authKeyInfo    = 'authentication';
        $encrypter              = Services::encrypter($config, false);

        $encrypted = 'f5eeb3f056b2dc5e8119b4a5f5ba793d724b9ca2d1ca23ab89bc72e51863f8da233a83ccb48d5daf3d6905d61f357877aaad32c8bc7a7c5e48f3268d2ba362b9UTw2A7U4CB9vb+6izrDzJHAdz1hAutIt2Ex2C2FqamJAXc8Z8RQor9UvaWy2';
        $decrypted = $encrypter->decrypt($encrypted);

        $expected = 'This is a plain-text message.';
        $this->assertSame($expected, $decrypted);
    }

    public function testDecryptEncryptedDataByCI42(): void
    {
        $config      = new EncryptionConfig();
        $config->key = hex2bin('64c70b0b8d45b80b9eba60b8b3c8a34d0193223d20fea46f8644b848bf7ce67f');
        $encrypter   = Services::encrypter($config, false);

        // Encrypted message by CI v4.2.0.
        $encrypted = base64_decode('UB9PC3QfQIoLY5+/GU8BUQnfhEcCml6i4Sve6k0f8r6Id6IzlbkvMhfWf5E2lBH5+OTWuv5MUoTBQWv9Pd46ua07QsqS6/vHaW3rCg6cpLM/8d2IZE/VO+uXeaU6XHO5mJ8ehGKg96JITvKjxA==', true);
        $decrypted = $encrypter->decrypt($encrypted);

        $expected = 'This is a plain-text message.';
        $this->assertSame($expected, $decrypted);
    }
}
