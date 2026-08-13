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

use SensitiveParameter;
use stdClass;

/**
 * (PHP) [array|stdClass --> encrypted JSON string] --> (DB driver) --> (DB column) string
 *       [               <-- decrypted JSON string] <-- (DB driver) <-- (DB column) encrypted JSON string
 *
 * @extends BaseCast<array<array-key, mixed>|stdClass|null, string|null>
 */
class EncryptedJsonCast extends BaseCast
{
    /**
     * @return array<array-key, mixed>|stdClass|null
     */
    public static function get(
        #[SensitiveParameter]
        mixed $value,
        array $params = [],
        ?object $helper = null,
    ): array|stdClass|null {
        if ($value === null) {
            return null;
        }

        $json = EncryptedCast::get($value, $params, $helper);

        return JsonCast::get($json, $params, $helper);
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

        $json = JsonCast::set($value, $params, $helper);

        return EncryptedCast::set($json, $params, $helper);
    }
}
