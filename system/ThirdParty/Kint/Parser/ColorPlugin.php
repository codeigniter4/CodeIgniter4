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

use InvalidArgumentException;
use Kint\Value\AbstractValue;
use Kint\Value\ColorValue;
use Kint\Value\Representation\ColorRepresentation;
use Kint\Value\StringValue;

class ColorPlugin extends AbstractPlugin implements PluginCompleteInterface
{
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
        if (\strlen($var) > 32) {
            return $v;
        }

        if (!$v instanceof StringValue) {
            return $v;
        }

        $trimmed = \strtolower(\trim($var));

        if (!isset(ColorRepresentation::$color_map[$trimmed]) && !\preg_match('/^(?:(?:rgb|hsl)a?[^\\)]{6,}\\)|#[0-9a-f]{3,8})$/', $trimmed)) {
            return $v;
        }

        try {
            $rep = new ColorRepresentation($var);
        } catch (InvalidArgumentException $e) {
            return $v;
        }

        $out = new ColorValue($v->getContext(), $v->getValue(), $v->getEncoding());
        $out->flags = $v->flags;
        $out->appendRepresentations($v->getRepresentations());
        $out->removeRepresentation('contents');
        $out->addRepresentation($rep, 0);

        return $out;
    }
}
