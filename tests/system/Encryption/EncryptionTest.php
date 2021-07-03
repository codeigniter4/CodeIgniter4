<?php

namespace CodeIgniter\Encryption;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Encryption as EncryptionConfig;
use Config\Services;

/**
 * @internal
 */
final class EncryptionTest extends CIUnitTestCase
{
    /**
     * @var \CodeIgniter\Encryption\Encryption
     */
    protected $encryption;

    protected function setUp(): void
    {
        $this->encryption = new Encryption();
    }

    /**
     * __construct test
     *
     * Covers behavior with config encryption key set or not
     */
    public function testConstructor()
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
    public function testBadDriver()
    {
        $this->expectException('CodeIgniter\Encryption\Exceptions\EncryptionException');

        // ask for a bad driver
        $config         = new EncryptionConfig();
        $config->driver = 'Bogus';
        $config->key    = 'anything';

        $this->encryption->initialize($config);
    }

    /**
     * Covers behavior with invalid parameters
     */
    public function testMissingDriver()
    {
        $this->expectException('CodeIgniter\Encryption\Exceptions\EncryptionException');

        // ask for a bad driver
        $config         = new EncryptionConfig();
        $config->driver = '';
        $config->key    = 'anything';

        $this->encryption->initialize($config);
    }

    public function testKeyCreation()
    {
        $this->assertNotEmpty($this->encryption->createKey());
        $this->assertSame(32, strlen($this->encryption->createKey()));
        $this->assertSame(16, strlen($this->encryption->createKey(16)));
    }

    public function testServiceSuccess()
    {
        $config         = new EncryptionConfig();
        $config->driver = 'OpenSSL';
        $config->key    = 'anything';

        $encrypter = Services::encrypter($config);
        $this->assertInstanceOf(EncrypterInterface::class, $encrypter);
    }

    public function testServiceFailure()
    {
        $this->expectException('CodeIgniter\Encryption\Exceptions\EncryptionException');

        // ask for a bad driver
        $config         = new EncryptionConfig();
        $config->driver = 'Kazoo';
        $config->key    = 'anything';

        Services::encrypter($config);
    }

    public function testServiceWithoutKey()
    {
        $this->expectException('CodeIgniter\Encryption\Exceptions\EncryptionException');

        Services::encrypter();
    }

    public function testServiceShared()
    {
        $config         = new EncryptionConfig();
        $config->driver = 'OpenSSL';
        $config->key    = 'anything';

        $encrypter = Services::encrypter($config, true);

        $config->key = 'Abracadabra';
        $encrypter   = Services::encrypter($config, true);
        $this->assertSame('anything', $encrypter->key);
    }

    public function testMagicIssetTrue()
    {
        $this->assertTrue(isset($this->encryption->digest));
    }

    public function testMagicIssetFalse()
    {
        $this->assertFalse(isset($this->encryption->bogus));
    }

    public function testMagicGet()
    {
        $this->assertSame('SHA512', $this->encryption->digest);
    }

    public function testMagicGetMissing()
    {
        $this->assertNull($this->encryption->bogus);
    }
}
