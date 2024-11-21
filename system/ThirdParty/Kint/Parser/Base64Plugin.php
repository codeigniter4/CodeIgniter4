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
use Kint\Value\Context\BaseContext;
use Kint\Value\Representation\ValueRepresentation;
use Kint\Value\StringValue;

class Base64Plugin extends AbstractPlugin implements PluginCompleteInterface
{
    /**
     * The minimum length before a string will be considered for base64 decoding.
     */
    public static int $min_length_hard = 16;

    /**
     * The minimum length before the base64 decoding will take precedence.
     */
    public static int $min_length_soft = 50;

    public function getTypes(): array
    {
        return ['string'];
    }

    public function getTriggers(): int
    {
        return Parser::TRIGGER_SUCCESS;
    }

    public function parseComplete(&$var, AbstractValue $v, int $trigger): AbstractValue
    {
        if (\strlen($var) < self::$min_length_hard || \strlen($var) % 4) {
            return $v;
        }

        if (\preg_match('/^[A-Fa-f0-9]+$/', $var)) {
            return $v;
        }

        if (!\preg_match('/^[A-Za-z0-9+\\/=]+$/', $var)) {
            return $v;
        }

        $data = \base64_decode($var, true);

        if (false === $data) {
            return $v;
        }

        $c = $v->getContext();

        $base = new BaseContext('base64_decode('.$c->getName().')');
        $base->depth = $c->getDepth() + 1;

        if (null !== ($ap = $c->getAccessPath())) {
            $base->access_path = 'base64_decode('.$ap.')';
        }

        $data = $this->getParser()->parse($data, $base);
        $data->flags |= AbstractValue::FLAG_GENERATED;

        if (!$data instanceof StringValue || false === $data->getEncoding()) {
            return $v;
        }

        $r = new ValueRepresentation('Base64', $data);

        if (\strlen($var) > self::$min_length_soft) {
            $v->addRepresentation($r, 0);
        } else {
            $v->addRepresentation($r);
        }

        return $v;
    }
}
