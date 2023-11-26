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

use Kint\Zval\InstanceValue;
use Kint\Zval\Value;

class BlacklistPlugin extends AbstractPlugin
{
    /**
     * List of classes and interfaces to blacklist.
     *
     * @var array
     */
    public static $blacklist = [];

    /**
     * List of classes and interfaces to blacklist except when dumped directly.
     *
     * @var array
     */
    public static $shallow_blacklist = ['Psr\\Container\\ContainerInterface'];

    public function getTypes(): array
    {
        return ['object'];
    }

    public function getTriggers(): int
    {
        return Parser::TRIGGER_BEGIN;
    }

    public function parse(&$var, Value &$o, int $trigger): void
    {
        foreach (self::$blacklist as $class) {
            if ($var instanceof $class) {
                $this->blacklistValue($var, $o);

                return;
            }
        }

        if ($o->depth <= 0) {
            return;
        }

        foreach (self::$shallow_blacklist as $class) {
            if ($var instanceof $class) {
                $this->blacklistValue($var, $o);

                return;
            }
        }
    }

    /**
     * @param object &$var
     */
    protected function blacklistValue(&$var, Value &$o): void
    {
        $object = new InstanceValue();
        $object->transplant($o);
        $object->classname = \get_class($var);
        $object->spl_object_hash = \spl_object_hash($var);
        $object->clearRepresentations();
        $object->value = null;
        $object->size = null;
        $object->hints[] = 'blacklist';

        $o = $object;

        $this->parser->haltParse();
    }
}
