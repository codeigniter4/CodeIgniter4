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

use Kint\Zval\Value;

class TimestampPlugin extends Plugin
{
    public static $blacklist = [
        2147483648,
        2147483647,
        1073741824,
        1073741823,
    ];

    public function getTypes()
    {
        return ['string', 'integer'];
    }

    public function getTriggers()
    {
        return Parser::TRIGGER_SUCCESS;
    }

    public function parse(&$var, Value &$o, $trigger)
    {
        if (\is_string($var) && !\ctype_digit($var)) {
            return;
        }

        if ($var < 0) {
            return;
        }

        if (\in_array($var, self::$blacklist, true)) {
            return;
        }

        $len = \strlen((string) $var);

        // Guess for anything between March 1973 and November 2286
        if (9 === $len || 10 === $len) {
            // If it's an int or string that's this short it probably has no other meaning
            // Additionally it's highly unlikely the shortValue will be clipped for length
            // If you're writing a plugin that interferes with this, just put your
            // parser plugin further down the list so that it gets loaded afterwards.
            $o->value->label = 'Timestamp';
            $o->value->hints[] = 'timestamp';
        }
    }
}
