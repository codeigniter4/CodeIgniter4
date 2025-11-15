<?php

declare(strict_types=1);

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

use DateTimeImmutable;
use Kint\Value\AbstractValue;
use Kint\Value\FixedWidthValue;
use Kint\Value\Representation\StringRepresentation;
use Kint\Value\StringValue;

class TimestampPlugin extends AbstractPlugin implements PluginCompleteInterface
{
    public static array $blacklist = [
        2147483648,
        2147483647,
        1073741824,
        1073741823,
    ];

    public function getTypes(): array
    {
        return ['string', 'integer'];
    }

    public function getTriggers(): int
    {
        return Parser::TRIGGER_SUCCESS;
    }

    public function parseComplete(&$var, AbstractValue $v, int $trigger): AbstractValue
    {
        if (\is_string($var) && !\ctype_digit($var)) {
            return $v;
        }

        if ($var < 0) {
            return $v;
        }

        if (\in_array($var, self::$blacklist, true)) {
            return $v;
        }

        $len = \strlen((string) $var);

        // Guess for anything between March 1973 and November 2286
        if ($len < 9 || $len > 10) {
            return $v;
        }

        if (!$v instanceof StringValue && !$v instanceof FixedWidthValue) {
            return $v;
        }

        if (!$dt = DateTimeImmutable::createFromFormat('U', (string) $var)) {
            return $v;
        }

        $v->removeRepresentation('contents');
        $v->addRepresentation(new StringRepresentation('Timestamp', $dt->format('c'), null, true));

        return $v;
    }
}
