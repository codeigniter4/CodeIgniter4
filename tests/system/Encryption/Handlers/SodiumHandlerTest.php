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
final class SodiumHandlerTest extends CIUnitTestCase
{
    private Encryption $encryption;
    private EncryptionConfig $config;

    protected function setUp(): void
    {
        if (! extension_loaded('sodium')) {
            $this->markTestSkipped('Libsodium is not available.');
        }

        parent::setUp();

        $this->config         = new EncryptionConfig();
        $this->config->driver = 'Sodium';
        $this->config->key    = sodium_crypto_secretbox_keygen();
        $this->encryption     = new Encryption($this->config);
    }

    public function testPropertiesGetter(): void
    {
        $this->config->key       = sodium_crypto_secretbox_keygen();
        $this->config->blockSize = 256;
        $encrypter               = $this->encryption->initialize($this->config);

        $this->assertSame($this->config->key, $encrypter->key);
        $this->assertSame($this->config->blockSize, $encrypter->blockSize);
        $this->assertNull($encrypter->driver);
    }

    public function testEmptyKeyThrowsErrorOnInitialize(): void
    {
        $this->expectException(EncryptionException::class);

        $this->config->key = '';
        $this->encryption->initialize($this->config);
    }

    public function testEmptyKeyThrowsErrorOnEncrypt(): void
    {
        $this->expectException(EncryptionException::class);

        $encrypter = $this->encryption->initialize($this->config);
        $encrypter->encrypt('Some message to encrypt', '');
    }

    public function testInvalidBlockSizeThrowsErrorOnEncrypt(): void
    {
        $this->expectException(EncryptionException::class);
        $this->config->blockSize = -1;

        $encrypter = $this->encryption->initialize($this->config);
        $encrypter->encrypt('Some message.');
    }

    public function testEmptyKeyThrowsErrorOnDecrypt(): void
    {
        $this->expectException(EncryptionException::class);

        $encrypter  = $this->encryption->initialize($this->config);
        $ciphertext = $encrypter->encrypt('Some message to encrypt');
        // After encrypt, the message and key are wiped from buffer
        $encrypter->decrypt($ciphertext);
    }

    public function testInvalidBlockSizeThrowsErrorOnDecrypt(): void
    {
        $this->expectException(EncryptionException::class);
        $key = $this->config->key;

        $encrypter  = $this->encryption->initialize($this->config);
        $ciphertext = $encrypter->encrypt('Some message.');
        // After encrypt, the message and key are wiped from buffer.
        $encrypter->decrypt($ciphertext, ['key' => $key, 'blockSize' => 0]);
    }

    public function testTruncatedMessageThrowsErrorOnDecrypt(): void
    {
        $this->expectException(EncryptionException::class);

        $encrypter  = $this->encryption->initialize($this->config);
        $ciphertext = $encrypter->encrypt('Some message to encrypt');
        $truncated  = mb_substr($ciphertext, 0, 24, '8bit');
        $encrypter->decrypt($truncated, ['blockSize' => 256, 'key' => sodium_crypto_secretbox_keygen()]);
    }

    public function testDecryptingMessages(): void
    {
        $key = sodium_crypto_secretbox_keygen();
        $msg = 'A plaintext message for you.';

        $this->config->key = $key;
        $encrypter         = $this->encryption->initialize($this->config);
        $ciphertext        = $encrypter->encrypt($msg);

        $this->assertSame($msg, $encrypter->decrypt($ciphertext, $key));
        $this->assertNotSame('A plain-text message for you.', $encrypter->decrypt($ciphertext, $key));
    }
}
