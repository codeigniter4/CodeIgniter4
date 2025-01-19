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
use Kint\Value\UninitializedValue;

/** @psalm-api */
class SerializePlugin extends AbstractPlugin implements PluginCompleteInterface
{
    /**
     * Disables automatic unserialization on arrays and objects.
     *
     * As the PHP manual notes:
     *
     * > Unserialization can result in code being loaded and executed due to
     * > object instantiation and autoloading, and a malicious user may be able
     * > to exploit this.
     *
     * The natural way to stop that from happening is to just refuse to unserialize
     * stuff by default. Which is what we're doing for anything that's not scalar.
     */
    public static bool $safe_mode = true;

    /**
     * @psalm-var bool|class-string[]
     */
    public static $allowed_classes = false;

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
        $trimmed = \rtrim($var);

        if ('N;' !== $trimmed && !\preg_match('/^(?:[COabis]:\\d+[:;]|d:\\d+(?:\\.\\d+);)/', $trimmed)) {
            return $v;
        }

        $options = ['allowed_classes' => self::$allowed_classes];

        $c = $v->getContext();

        $base = new BaseContext('unserialize('.$c->getName().')');
        $base->depth = $c->getDepth() + 1;

        if (null !== ($ap = $c->getAccessPath())) {
            $base->access_path = 'unserialize('.$ap;
            if (true === self::$allowed_classes) {
                $base->access_path .= ')';
            } else {
                $base->access_path .= ', '.\var_export($options, true).')';
            }
        }

        if (self::$safe_mode && \in_array($trimmed[0], ['C', 'O', 'a'], true)) {
            $data = new UninitializedValue($base);
            $data->flags |= AbstractValue::FLAG_BLACKLIST;
        } else {
            // Suppress warnings on unserializeable variable
            $data = @\unserialize($trimmed, $options);

            if (false === $data && 'b:0;' !== \substr($trimmed, 0, 4)) {
                return $v;
            }

            $data = $this->getParser()->parse($data, $base);
        }

        $data->flags |= AbstractValue::FLAG_GENERATED;

        $v->addRepresentation(new ValueRepresentation('Serialized', $data), 0);

        return $v;
    }
}
