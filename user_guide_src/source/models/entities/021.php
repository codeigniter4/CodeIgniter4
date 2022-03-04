<?php

namespace App\Entities\Cast;

use CodeIgniter\Entity\Cast\BaseCast;

class SomeHandler extends BaseCast
{
    public static function get($value, array $params = [])
    {
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
