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
use Traversable;

class IteratorPlugin extends AbstractPlugin
{
    /**
     * List of classes and interfaces to blacklist.
     *
     * Certain classes (Such as PDOStatement) irreversibly lose information
     * when traversed. Others are just huge. Either way, put them in here
     * and you won't have to worry about them being parsed.
     *
     * @var array
     */
    public static $blacklist = [
        'DOMNamedNodeMap',
        'DOMNodeList',
        'mysqli_result',
        'PDOStatement',
        'SplFileObject',
    ];

    public function getTypes(): array
    {
        return ['object'];
    }

    public function getTriggers(): int
    {
        return Parser::TRIGGER_SUCCESS;
    }

    public function parse(&$var, Value &$o, int $trigger): void
    {
        if (!$var instanceof Traversable) {
            return;
        }

        foreach (self::$blacklist as $class) {
            if ($var instanceof $class) {
                $b = new Value();
                $b->name = $class.' Iterator Contents';
                $b->access_path = 'iterator_to_array('.$o->access_path.', true)';
                $b->depth = $o->depth + 1;
                $b->hints[] = 'blacklist';

                $r = new Representation('Iterator');
                $r->contents = [$b];

                $o->addRepresentation($r);

                return;
            }
        }

        $data = \iterator_to_array($var);

        $base_obj = new Value();
        $base_obj->depth = $o->depth;

        if ($o->access_path) {
            $base_obj->access_path = 'iterator_to_array('.$o->access_path.')';
        }

        $r = new Representation('Iterator');
        $r->contents = $this->parser->parse($data, $base_obj);
        $r->contents = $r->contents->value->contents;

        $primary = $o->getRepresentations();
        $primary = \reset($primary);
        if ($primary && $primary === $o->value && [] === $primary->contents) {
            $o->addRepresentation($r, 0);
        } else {
            $o->addRepresentation($r);
        }
    }
}
