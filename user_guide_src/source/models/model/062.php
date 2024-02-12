<?php

namespace App\Models\Cast;

use CodeIgniter\DataCaster\Cast\BaseCast;

class SomeHandler extends BaseCast
{
    public static function get(
        mixed $value,
        array $params = [],
        ?object $helper = null
    ): mixed {
        var_dump($params);
        /*
         * Output:
         * array(3) {
         *   [0]=>
         *   string(13) "App\SomeClass"
         *   [1]=>
         *   string(6) "param2"
         *   [2]=>
         *   string(6) "param3"
         * }
         */
    }
}
