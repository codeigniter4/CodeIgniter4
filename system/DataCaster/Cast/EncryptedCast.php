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

namespace CodeIgniter\DataCaster\Cast;

use CodeIgniter\DataCaster\Exceptions\CastException;
use SensitiveParameter;

/**
 * Class EncryptedCast
 *
 * (PHP) [string --> encrypted string] --> (DB driver) --> (DB column) string
 *       [       <-- string          ] <-- (DB driver) <-- (DB column) encrypted string
 */
class EncryptedCast extends BaseCast
{
    public static function get(
        #[SensitiveParameter]
        mixed $value,
        array $params = [],
        ?object $helper = null,
    ): ?string {
        if ($value === null) {
            return null;
        }

        if (! is_string($value)) {
            throw CastException::forInvalidEncryptedValueType();
        }

        $decoded = base64_decode($value, true);

        if ($decoded === false) {
            throw CastException::forInvalidEncryptedPayload();
        }

        return service('encrypter')->decrypt($decoded);
    }

    public static function set(
        #[SensitiveParameter]
        mixed $value,
        array $params = [],
        ?object $helper = null,
    ): ?string {
        if ($value === null) {
            return null;
        }

        if (! is_string($value)) {
            throw CastException::forInvalidEncryptedValueType();
        }

        return base64_encode(service('encrypter')->encrypt($value));
    }
}
