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

use Kint\Value\AbstractValue;
use Kint\Value\MicrotimeValue;
use Kint\Value\Representation\MicrotimeRepresentation;

class MicrotimePlugin extends AbstractPlugin implements PluginCompleteInterface
{
    private static ?array $last = null;
    private static ?float $start = null;
    private static int $times = 0;
    private static ?string $group = null;

    public function getTypes(): array
    {
        return ['string', 'double'];
    }

    public function getTriggers(): int
    {
        return Parser::TRIGGER_SUCCESS;
    }

    public function parseComplete(&$var, AbstractValue $v, int $trigger): AbstractValue
    {
        $c = $v->getContext();

        if ($c->getDepth() > 0) {
            return $v;
        }

        if (\is_string($var)) {
            if ('microtime()' !== $c->getName() || !\preg_match('/^0\\.[0-9]{8} [0-9]{10}$/', $var)) {
                return $v;
            }

            $usec = (int) \substr($var, 2, 6);
            $sec = (int) \substr($var, 11, 10);
        } else {
            if ('microtime(...)' !== $c->getName()) {
                return $v;
            }

            $sec = (int) \floor($var);
            $usec = $var - $sec;
            $usec = (int) \floor($usec * 1000000);
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
            $r = new MicrotimeRepresentation($sec, $usec, self::getGroup(), $lap, $total, self::$times);
        } else {
            $r = new MicrotimeRepresentation($sec, $usec, self::getGroup());
        }

        $out = new MicrotimeValue($v);
        $out->removeRepresentation('contents');
        $out->addRepresentation($r);

        return $out;
    }

    /** @psalm-api */
    public static function clean(): void
    {
        self::$last = null;
        self::$start = null;
        self::$times = 0;
        self::newGroup();
    }

    private static function getGroup(): string
    {
        if (null === self::$group) {
            return self::newGroup();
        }

        return self::$group;
    }

    private static function newGroup(): string
    {
        return self::$group = \bin2hex(\random_bytes(4));
    }
}
