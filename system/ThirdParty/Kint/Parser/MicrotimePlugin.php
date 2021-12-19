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

use Kint\Zval\Representation\MicrotimeRepresentation;
use Kint\Zval\Value;

class MicrotimePlugin extends Plugin
{
    private static $last = null;
    private static $start = null;
    private static $times = 0;
    private static $group = 0;

    public function getTypes()
    {
        return ['string', 'double'];
    }

    public function getTriggers()
    {
        return Parser::TRIGGER_SUCCESS;
    }

    public function parse(&$var, Value &$o, $trigger)
    {
        if (0 !== $o->depth) {
            return;
        }

        if (\is_string($var)) {
            if ('microtime()' !== $o->name || !\preg_match('/^0\\.[0-9]{8} [0-9]{10}$/', $var)) {
                return;
            }

            $usec = (int) \substr($var, 2, 6);
            $sec = (int) \substr($var, 11, 10);
        } else {
            if ('microtime(...)' !== $o->name) {
                return;
            }

            $sec = \floor($var);
            $usec = $var - $sec;
            $usec = \floor($usec * 1000000);
        }

        $time = $sec + ($usec / 1000000);

        if (null !== self::$last) {
            $last_time = self::$last[0] + (self::$last[1] / 1000000);
            $lap = $time - $last_time;
            ++self::$times;
        } else {
            $lap = null;
            self::$start = $time;
        }

        self::$last = [$sec, $usec];

        if (null !== $lap) {
            $total = $time - self::$start;
            $r = new MicrotimeRepresentation($sec, $usec, self::$group, $lap, $total, self::$times);
        } else {
            $r = new MicrotimeRepresentation($sec, $usec, self::$group);
        }
        $r->contents = $var;
        $r->implicit_label = true;

        $o->removeRepresentation($o->value);
        $o->addRepresentation($r);
        $o->hints[] = 'microtime';
    }

    public static function clean()
    {
        self::$last = null;
        self::$start = null;
        self::$times = 0;
        ++self::$group;
    }
}
