<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Encryption\Exceptions;

use CodeIgniter\Exceptions\DebugTraceableTrait;
use CodeIgniter\Exceptions\ExceptionInterface;
use RuntimeException;

/**
 * Encryption exception
 */
class EncryptionException extends RuntimeException implements ExceptionInterface
{
    use DebugTraceableTrait;

    /**
     * Thrown when no driver is present in the active encryption session.
     *
     * @return static
     */
    public static function forNoDriverRequested()
    {
        return new static(lang('Encryption.noDriverRequested'));
    }

    /**
     * Thrown when the handler requested is not available.
     *
     * @return static
     */
    public static function forNoHandlerAvailable(string $handler)
    {
        return new static(lang('Encryption.noHandlerAvailable', [$handler]));
    }

    /**
     * Thrown when the handler requested is unknown.
     *
     * @return static
     */
    public static function forUnKnownHandler(?string $driver = null)
    {
        return new static(lang('Encryption.unKnownHandler', [$driver]));
    }

    /**
     * Thrown when no starter key is provided for the current encryption session.
     *
     * @return static
     */
    public static function forNeedsStarterKey()
    {
        return new static(lang('Encryption.starterKeyNeeded'));
    }

    /**
     * Thrown during data decryption when a problem or error occurred.
     *
     * @return static
     */
    public static function forAuthenticationFailed()
    {
        return new static(lang('Encryption.authenticationFailed'));
    }

    /**
     * Thrown during data encryption when a problem or error occurred.
     *
     * @return static
     */
    public static function forEncryptionFailed()
    {
        return new static(lang('Encryption.encryptionFailed'));
    }
}
