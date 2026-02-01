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

namespace CodeIgniter\Encryption;

use CodeIgniter\Encryption\Exceptions\EncryptionException;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Encryption as EncryptionConfig;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

/**
 * @internal
 */
#[Group('Others')]
final class KeyRotationDecoratorTest extends CIUnitTestCase
{
    private Encryption $encryption;

    protected function setUp(): void
    {
        $this->encryption = new Encryption();
    }

    #[RequiresPhpExtension('openssl')]
    public function testEncryptionUsesCurrentKey(): void
    {
        $currentKey  = 'current-encryption-key';
        $previousKey = 'previous-encryption-key';

        $params               = new EncryptionConfig();
        $params->driver       = 'OpenSSL';
        $params->key          = $currentKey;
        $params->previousKeys = [$previousKey];

        $encrypter = $this->encryption->initialize($params);

        $message   = 'This is a plain-text message.';
        $encrypted = $encrypter->encrypt($message);

        $this->assertSame($message, $encrypter->decrypt($encrypted));

        $this->expectException(EncryptionException::class);
        $encrypter->decrypt($encrypted, ['key' => $previousKey]);
    }

    #[RequiresPhpExtension('openssl')]
    public function testKeyRotationDecryptsOldData(): void
    {
        $oldKey = 'old-encryption-key';
        $newKey = 'new-encryption-key';

        $paramsOld         = new EncryptionConfig();
        $paramsOld->driver = 'OpenSSL';
        $paramsOld->key    = $oldKey;

        $oldEncrypter = $this->encryption->initialize($paramsOld);
        $message      = 'Sensitive data encrypted with old key';
        $encrypted    = $oldEncrypter->encrypt($message);

        $paramsNew               = new EncryptionConfig();
        $paramsNew->driver       = 'OpenSSL';
        $paramsNew->key          = $newKey;
        $paramsNew->previousKeys = [$oldKey];

        $newEncrypter = $this->encryption->initialize($paramsNew);

        $this->assertSame($message, $newEncrypter->decrypt($encrypted));
    }

    #[RequiresPhpExtension('openssl')]
    public function testMultiplePreviousKeysFallback(): void
    {
        $key1 = 'first-key-very-long';
        $key2 = 'second-key-very-long';
        $key3 = 'third-key-very-long';

        $params1         = new EncryptionConfig();
        $params1->driver = 'OpenSSL';
        $params1->key    = $key1;
        $encrypter1      = $this->encryption->initialize($params1);
        $message1        = 'Message encrypted with key1';
        $encrypted1      = $encrypter1->encrypt($message1);

        $params2         = new EncryptionConfig();
        $params2->driver = 'OpenSSL';
        $params2->key    = $key2;
        $encrypter2      = $this->encryption->initialize($params2);
        $message2        = 'Message encrypted with key2';
        $encrypted2      = $encrypter2->encrypt($message2);

        $params3               = new EncryptionConfig();
        $params3->driver       = 'OpenSSL';
        $params3->key          = $key3;
        $params3->previousKeys = [$key2, $key1];

        $encrypter3 = $this->encryption->initialize($params3);

        $this->assertSame($message1, $encrypter3->decrypt($encrypted1));
        $this->assertSame($message2, $encrypter3->decrypt($encrypted2));
    }

    #[RequiresPhpExtension('openssl')]
    public function testExplicitKeyPreventsRotation(): void
    {
        $currentKey  = 'current-key-very-long';
        $previousKey = 'previous-key-very-long';
        $explicitKey = 'explicit-key-very-long';

        $paramsOld         = new EncryptionConfig();
        $paramsOld->driver = 'OpenSSL';
        $paramsOld->key    = $previousKey;
        $oldEncrypter      = $this->encryption->initialize($paramsOld);
        $message           = 'Test message';
        $encrypted         = $oldEncrypter->encrypt($message);

        $params               = new EncryptionConfig();
        $params->driver       = 'OpenSSL';
        $params->key          = $currentKey;
        $params->previousKeys = [$previousKey];
        $encrypter            = $this->encryption->initialize($params);

        $this->expectException(EncryptionException::class);
        $encrypter->decrypt($encrypted, ['key' => $explicitKey]);
    }

    #[RequiresPhpExtension('openssl')]
    public function testEmptyPreviousKeysNoFallback(): void
    {
        $key1 = 'first-key-very-long';
        $key2 = 'second-key-very-long';

        $params1         = new EncryptionConfig();
        $params1->driver = 'OpenSSL';
        $params1->key    = $key1;
        $encrypter1      = $this->encryption->initialize($params1);
        $message         = 'Test message';
        $encrypted       = $encrypter1->encrypt($message);

        $params2               = new EncryptionConfig();
        $params2->driver       = 'OpenSSL';
        $params2->key          = $key2;
        $params2->previousKeys = [];
        $encrypter2            = $this->encryption->initialize($params2);

        $this->expectException(EncryptionException::class);
        $encrypter2->decrypt($encrypted);
    }

    #[RequiresPhpExtension('openssl')]
    public function testAllKeysFailThrowsOriginalException(): void
    {
        $correctKey = 'correct-key-very-long';
        $wrongKey1  = 'wrong-key-1-very-long';
        $wrongKey2  = 'wrong-key-2-very-long';
        $wrongKey3  = 'wrong-key-3-very-long';

        $paramsCorrect         = new EncryptionConfig();
        $paramsCorrect->driver = 'OpenSSL';
        $paramsCorrect->key    = $correctKey;
        $encrypter             = $this->encryption->initialize($paramsCorrect);
        $message               = 'Test message';
        $encrypted             = $encrypter->encrypt($message);

        $paramsWrong               = new EncryptionConfig();
        $paramsWrong->driver       = 'OpenSSL';
        $paramsWrong->key          = $wrongKey1;
        $paramsWrong->previousKeys = [$wrongKey2, $wrongKey3];
        $encrypterWrong            = $this->encryption->initialize($paramsWrong);

        $this->expectException(EncryptionException::class);
        $this->expectExceptionMessage('authentication failed');
        $encrypterWrong->decrypt($encrypted);
    }

    #[RequiresPhpExtension('openssl')]
    public function testPropertyAccessDelegation(): void
    {
        $params               = new EncryptionConfig();
        $params->driver       = 'OpenSSL';
        $params->key          = 'test-key-very-long';
        $params->cipher       = 'AES-128-CBC';
        $params->previousKeys = ['old-key'];

        $encrypter = $this->encryption->initialize($params);

        $this->assertSame('AES-128-CBC', $encrypter->cipher);
        $this->assertSame('test-key-very-long', $encrypter->key);
    }

    #[RequiresPhpExtension('sodium')]
    public function testKeyRotationWithSodiumHandler(): void
    {
        $oldKey = sodium_crypto_secretbox_keygen();
        $newKey = sodium_crypto_secretbox_keygen();

        $paramsOld         = new EncryptionConfig();
        $paramsOld->driver = 'Sodium';
        $paramsOld->key    = $oldKey;
        $oldEncrypter      = $this->encryption->initialize($paramsOld);
        $message           = 'Sensitive data encrypted with old Sodium key';
        $encrypted         = $oldEncrypter->encrypt($message);

        $paramsNew               = new EncryptionConfig();
        $paramsNew->driver       = 'Sodium';
        $paramsNew->key          = $newKey;
        $paramsNew->previousKeys = [$oldKey];
        $newEncrypter            = $this->encryption->initialize($paramsNew);

        $this->assertSame($message, $newEncrypter->decrypt($encrypted));

        $newMessage   = 'New message with new key';
        $newEncrypted = $newEncrypter->encrypt($newMessage);
        $this->assertSame($newMessage, $newEncrypter->decrypt($newEncrypted));
    }

    #[RequiresPhpExtension('openssl')]
    public function testRealisticKeyRotationScenario(): void
    {
        $q1Key = 'q1-2026-key-very-long';
        $q2Key = 'q2-2026-key-very-long';
        $q3Key = 'q3-2026-key-very-long';
        $q4Key = 'q4-2026-key-very-long';

        // Q1: Encrypt user data
        $configQ1         = new EncryptionConfig();
        $configQ1->driver = 'OpenSSL';
        $configQ1->key    = $q1Key;
        $encrypterQ1      = $this->encryption->initialize($configQ1);
        $userData         = 'user-sensitive-data-from-q1';
        $encryptedQ1      = $encrypterQ1->encrypt($userData);

        // Q2: Rotate to new key, keep Q1 for BC
        $configQ2               = new EncryptionConfig();
        $configQ2->driver       = 'OpenSSL';
        $configQ2->key          = $q2Key;
        $configQ2->previousKeys = [$q1Key];
        $encrypterQ2            = $this->encryption->initialize($configQ2);

        // Can still read Q1 data
        $this->assertSame($userData, $encrypterQ2->decrypt($encryptedQ1));

        // New data encrypted with Q2 key
        $newData     = 'user-sensitive-data-from-q2';
        $encryptedQ2 = $encrypterQ2->encrypt($newData);
        $this->assertSame($newData, $encrypterQ2->decrypt($encryptedQ2));

        // Q3: Rotate to new key, keep Q2 and Q1 for BC
        $configQ3               = new EncryptionConfig();
        $configQ3->driver       = 'OpenSSL';
        $configQ3->key          = $q3Key;
        $configQ3->previousKeys = [$q2Key, $q1Key];
        $encrypterQ3            = $this->encryption->initialize($configQ3);

        // Can still read Q1 and Q2 data
        $this->assertSame($userData, $encrypterQ3->decrypt($encryptedQ1));
        $this->assertSame($newData, $encrypterQ3->decrypt($encryptedQ2));

        // Q4: Rotate to new key, keep only Q3 and Q2 (drop Q1 - data should be re-encrypted by now)
        $configQ4               = new EncryptionConfig();
        $configQ4->driver       = 'OpenSSL';
        $configQ4->key          = $q4Key;
        $configQ4->previousKeys = [$q3Key, $q2Key];
        $encrypterQ4            = $this->encryption->initialize($configQ4);

        // Can still read Q2 and Q3 data
        $this->assertSame($newData, $encrypterQ4->decrypt($encryptedQ2));

        // But Q1 data is no longer accessible (as intended)
        $this->expectException(EncryptionException::class);
        $encrypterQ4->decrypt($encryptedQ1);
    }
}
