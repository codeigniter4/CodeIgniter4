<?php

namespace Tests\Support\Entity\Cast;

use CodeIgniter\Entity\Cast\BaseCast;

class CastBase64 extends BaseCast
{
    /**
     * Get
     *
     * @param mixed $value  Data
     * @param array $params Additional param
     *
     * @return mixed
     */
    public static function get($value, array $params = []): string
    {
        return base64_decode($value, true);
    }

    /**
     * Set
     *
     * @param mixed $value  Data
     * @param array $params Additional param
     *
     * @return mixed
     */
    public static function set($value, array $params = []): string
    {
        return base64_encode($value);
    }
}
