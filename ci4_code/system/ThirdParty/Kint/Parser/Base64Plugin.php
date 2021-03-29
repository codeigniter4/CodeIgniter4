<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2013 Jonathan Vollebregt (jnvsor@gmail.com), Rokas Šleinius (raveren@gmail.com)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 * the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Kint\Parser;

use Kint\Object\BasicObject;
use Kint\Object\Representation\Representation;

class Base64Plugin extends Plugin
{
    /**
     * The minimum length before a string will be considered for base64 decoding.
     *
     * @var int
     */
    public static $min_length_hard = 16;

    /**
     * The minimum length before the base64 decoding will take precedence.
     *
     * @var int
     */
    public static $min_length_soft = 50;

    public function getTypes()
    {
        return array('string');
    }

    public function getTriggers()
    {
        return Parser::TRIGGER_SUCCESS;
    }

    public function parse(&$var, BasicObject &$o, $trigger)
    {
        if (\strlen($var) < self::$min_length_hard || \strlen($var) % 4) {
            return;
        }

        if (\preg_match('/^[A-Fa-f0-9]+$/', $var)) {
            return;
        }

        if (!\preg_match('/^[A-Za-z0-9+\\/=]+$/', $var)) {
            return;
        }

        /** @var false|string */
        $data = \base64_decode($var, true);

        if (false === $data) {
            return;
        }

        $base_obj = new BasicObject();
        $base_obj->depth = $o->depth + 1;
        $base_obj->name = 'base64_decode('.$o->name.')';

        if ($o->access_path) {
            $base_obj->access_path = 'base64_decode('.$o->access_path.')';
        }

        $r = new Representation('Base64');
        $r->contents = $this->parser->parse($data, $base_obj);

        if (\strlen($var) > self::$min_length_soft) {
            $o->addRepresentation($r, 0);
        } else {
            $o->addRepresentation($r);
        }
    }
}
