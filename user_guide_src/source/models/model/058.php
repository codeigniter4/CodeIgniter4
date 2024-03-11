<?php

namespace App\Models\Cast;

use CodeIgniter\DataCaster\Cast\BaseCast;
use InvalidArgumentException;

// The class must inherit the CodeIgniter\DataCaster\Cast\BaseCast class
class CastBase64 extends BaseCast
{
    public static function get(
        mixed $value,
        array $params = [],
        ?object $helper = null
    ): string {
        if (! is_string($value)) {
            self::invalidTypeValueError($value);
        }

        $decoded = base64_decode($value, true);

        if ($decoded === false) {
            throw new InvalidArgumentException('Cannot decode: ' . $value);
        }

        return $decoded;
    }

    public static function set(
        mixed $value,
        array $params = [],
        ?object $helper = null
    ): string {
        if (! is_string($value)) {
            self::invalidTypeValueError($value);
        }

        return base64_encode($value);
    }
}
