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
use Config\Encryption as EncryptionConfig;

/**
 * CodeIgniter Encryption Manager
 *
 * Provides two-way keyed encryption via PHP's Sodium and/or OpenSSL extensions.
 * This class determines the driver, cipher, and mode to use, and then
 * initializes the appropriate encryption handler.
 */
class Encryption
{
    /**
     * The encrypter we create
     *
     * @var EncrypterInterface
     */
    protected $encrypter;

    /**
     * The driver being used
     *
     * @var string
     */
    protected $driver;

    /**
     * The key/seed being used
     *
     * @var string
     */
    protected $key;

    /**
     * The derived HMAC key
     *
     * @var string
     */
    protected $hmacKey;

    /**
     * HMAC digest to use
     *
     * @var string
     */
    protected $digest = 'SHA512';

    /**
     * Map of drivers to handler classes, in preference order
     *
     * @var array
     */
    protected $drivers = [
        'OpenSSL',
        'Sodium',
    ];

    /**
     * Handlers that are to be installed
     *
     * @var array<string, boolean>
     */
    protected $handlers = [];

    /**
     * @throws EncryptionException
     */
    public function __construct(?EncryptionConfig $config = null)
    {
        $config ??= new EncryptionConfig();

        $this->key    = $config->key;
        $this->driver = $config->driver;
        $this->digest = $config->digest ?? 'SHA512';

        $this->handlers = [
            'OpenSSL' => extension_loaded('openssl'),
            // the SodiumHandler uses some API (like sodium_pad) that is available only on v1.0.14+
            'Sodium' => extension_loaded('sodium') && version_compare(SODIUM_LIBRARY_VERSION, '1.0.14', '>='),
        ];

        if (! in_array($this->driver, $this->drivers, true) || (array_key_exists($this->driver, $this->handlers) && ! $this->handlers[$this->driver])) {
            throw EncryptionException::forNoHandlerAvailable($this->driver);
        }
    }

    /**
     * Initialize or re-initialize an encrypter
     *
     * @return EncrypterInterface
     *
     * @throws EncryptionException
     */
    public function initialize(?EncryptionConfig $config = null)
    {
        if ($config) {
            $this->key    = $config->key;
            $this->driver = $config->driver;
            $this->digest = $config->digest ?? 'SHA512';
        }

        if (empty($this->driver)) {
            throw EncryptionException::forNoDriverRequested();
        }

        if (! in_array($this->driver, $this->drivers, true)) {
            throw EncryptionException::forUnKnownHandler($this->driver);
        }

        if (empty($this->key)) {
            throw EncryptionException::forNeedsStarterKey();
        }

        $this->hmacKey = bin2hex(\hash_hkdf($this->digest, $this->key));

        $handlerName     = 'CodeIgniter\\Encryption\\Handlers\\' . $this->driver . 'Handler';
        $this->encrypter = new $handlerName($config);

        return $this->encrypter;
    }

    /**
     * Create a random key
     *
     * @param int $length Output length
     *
     * @return string
     */
    public static function createKey($length = 32)
    {
        return random_bytes($length);
    }

    /**
     * __get() magic, providing readonly access to some of our protected properties
     *
     * @param string $key Property name
     *
     * @return mixed
     */
    public function __get($key)
    {
        if ($this->__isset($key)) {
            return $this->{$key};
        }

        return null;
    }

    /**
     * __isset() magic, providing checking for some of our protected properties
     *
     * @param string $key Property name
     */
    public function __isset($key): bool
    {
        return in_array($key, ['key', 'digest', 'driver', 'drivers'], true);
    }
}
