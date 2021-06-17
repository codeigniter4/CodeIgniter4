<?php

namespace Tests\Support\Entity\Cast;

use CodeIgniter\Entity\Cast\BaseCast;

class CastPassParameters extends BaseCast
{
    /**
     * Set
     *
     * @param mixed $value  Data
     * @param array $params Additional param
     *
     * @return mixed
     */
    public static function set($value, array $params = [])
    {
        return $value . ':' . json_encode($params);
    }
}
