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

use Kint\Zval\Representation\Representation;
use Kint\Zval\Value;

class SerializePlugin extends AbstractPlugin
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
     *
     * @var bool
     */
    public static $safe_mode = true;

    /**
     * @var bool|class-string[]
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

    public function parse(&$var, Value &$o, int $trigger): void
    {
        $trimmed = \rtrim($var);

        if ('N;' !== $trimmed && !\preg_match('/^(?:[COabis]:\\d+[:;]|d:\\d+(?:\\.\\d+);)/', $trimmed)) {
            return;
        }

        $options = ['allowed_classes' => self::$allowed_classes];

        if (!self::$safe_mode || !\in_array($trimmed[0], ['C', 'O', 'a'], true)) {
            // Suppress warnings on unserializeable variable
            $data = @\unserialize($trimmed, $options);

            if (false === $data && 'b:0;' !== \substr($trimmed, 0, 4)) {
                return;
            }
        }

        $base_obj = new Value();
        $base_obj->depth = $o->depth + 1;
        $base_obj->name = 'unserialize('.$o->name.')';

        if ($o->access_path) {
            $base_obj->access_path = 'unserialize('.$o->access_path;
            if (true === self::$allowed_classes) {
                $base_obj->access_path .= ')';
            } else {
                $base_obj->access_path .= ', '.\var_export($options, true).')';
            }
        }

        $r = new Representation('Serialized');

        if (isset($data)) {
            $r->contents = $this->parser->parse($data, $base_obj);
        } else {
            $base_obj->hints[] = 'blacklist';
            $r->contents = $base_obj;
        }

        $o->addRepresentation($r, 0);
    }
}
