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

namespace CodeIgniter\DataCaster;

use CodeIgniter\Config\Factories;
use CodeIgniter\DataCaster\Exceptions\CastException;
use CodeIgniter\DataConverter\DataConverter;
use CodeIgniter\Encryption\Exceptions\EncryptionException;
use CodeIgniter\Entity\Entity;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Encryption as EncryptionConfig;
use Config\Services;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

/**
 * @internal
 */
#[Group('Others')]
#[RequiresPhpExtension('openssl')]
final class EncryptedCastTest extends CIUnitTestCase
{
    private const CURRENT_KEY = 'current-encrypted-cast-key';
    private const OLD_KEY     = 'old-encrypted-cast-key';

    protected function setUp(): void
    {
        parent::setUp();

        $this->useEncryptionKey(self::CURRENT_KEY);
    }

    public function testSetEncryptsStringAsEncodedText(): void
    {
        $dataCaster = new DataCaster(types: ['secret' => 'encrypted']);

        $encrypted = $dataCaster->castAs('plain-secret', 'secret', 'set');

        $this->assertIsString($encrypted);
        $this->assertNotSame('plain-secret', $encrypted);
        $this->assertNotFalse(base64_decode($encrypted, true));
        $this->assertSame('plain-secret', $dataCaster->castAs($encrypted, 'secret'));
    }

    public function testEncryptedCastSupportsNullableValues(): void
    {
        $dataCaster = new DataCaster(types: ['secret' => '?encrypted']);

        $this->assertNull($dataCaster->castAs(null, 'secret', 'set'));
        $this->assertNull($dataCaster->castAs(null, 'secret'));
    }

    public function testEncryptedCastRejectsInvalidPlainValueWithoutLeakingIt(): void
    {
        $dataCaster = new DataCaster(types: ['secret' => 'encrypted']);

        try {
            $dataCaster->castAs(['token' => 'sensitive-value'], 'secret', 'set');
        } catch (CastException $e) {
            $this->assertSame('Type casting "encrypted" expects a string or null value.', $e->getMessage());
            $this->assertStringNotContainsString('token', $e->getMessage());
            $this->assertStringNotContainsString('sensitive-value', $e->getMessage());

            return;
        }

        $this->fail('Expected encrypted casting to reject non-string values.');
    }

    public function testEncryptedCastRejectsMalformedPayload(): void
    {
        $this->expectException(CastException::class);
        $this->expectExceptionMessage('Type casting "encrypted" expects a valid encrypted value.');

        $dataCaster = new DataCaster(types: ['secret' => 'encrypted']);

        $dataCaster->castAs('@@not-base64@@', 'secret');
    }

    public function testEncryptedCastBubblesAuthenticationFailures(): void
    {
        $this->expectException(EncryptionException::class);

        $dataCaster = new DataCaster(types: ['secret' => 'encrypted']);

        $dataCaster->castAs(base64_encode('not-encrypted'), 'secret');
    }

    public function testEncryptedCastCanDecryptPreviousKeyValues(): void
    {
        $this->useEncryptionKey(self::OLD_KEY);
        $oldEncryptedValue = base64_encode(Services::encrypter()->encrypt('old-secret'));

        $this->useEncryptionKey(self::CURRENT_KEY, [self::OLD_KEY]);

        $dataCaster = new DataCaster(types: ['secret' => 'encrypted']);

        $this->assertSame('old-secret', $dataCaster->castAs($oldEncryptedValue, 'secret'));
    }

    public function testDataConverterConvertsEncryptedFieldToAndFromDataSource(): void
    {
        $converter = new DataConverter(['secret' => 'encrypted']);

        $dataSourceData = $converter->toDataSource(['secret' => 'plain-secret']);

        $this->assertIsString($dataSourceData['secret']);
        $this->assertNotSame('plain-secret', $dataSourceData['secret']);
        $this->assertSame(['secret' => 'plain-secret'], $converter->fromDataSource($dataSourceData));
    }

    public function testEntityStoresEncryptedRawValueAndReturnsPlaintext(): void
    {
        $entity = new class () extends Entity {
            protected $casts = [
                'secret' => 'encrypted',
            ];
        };

        $entity->secret = 'plain-secret';

        $raw = $entity->toRawArray();

        $this->assertIsString($raw['secret']);
        $this->assertNotSame('plain-secret', $raw['secret']);
        $this->assertSame('plain-secret', $entity->secret);
        $this->assertSame(['secret' => 'plain-secret'], $entity->toArray());
    }

    /**
     * @param list<string> $previousKeys
     */
    private function useEncryptionKey(string $key, array $previousKeys = []): void
    {
        $config               = new EncryptionConfig();
        $config->driver       = 'OpenSSL';
        $config->key          = $key;
        $config->previousKeys = $previousKeys;

        Factories::injectMock('config', EncryptionConfig::class, $config);
    }
}
