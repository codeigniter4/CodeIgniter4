<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Encryption\Handlers;

use CodeIgniter\Encryption\EncrypterInterface;
use Config\Encryption;
use Psr\Log\LoggerInterface;

/**
 * Base class for encryption handling
 */
abstract class BaseHandler implements EncrypterInterface
{
    /**
     * Logger instance to record error messages and warnings.
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param Encryption|null $config
     */
    public function __construct(?Encryption $config = null)
    {
        $config = $config ?? config('Encryption');

        // make the parameters conveniently accessible
        foreach (get_object_vars($config) as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * Byte-safe substr()
     *
     * @param string $str
     * @param int    $start
     * @param int    $length
     *
     * @return string
     */
    protected static function substr($str, $start, $length = null)
    {
        return mb_substr($str, $start, $length, '8bit');
    }

    /**
     * __get() magic, providing readonly access to some of our properties
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
     * __isset() magic, providing checking for some of our properties
     *
     * @param string $key Property name
     *
     * @return bool
     */
    public function __isset($key): bool
    {
        return property_exists($this, $key);
    }
}
