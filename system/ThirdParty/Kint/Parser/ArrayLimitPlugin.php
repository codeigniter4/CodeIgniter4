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

use InvalidArgumentException;
use Kint\Utils;
use Kint\Zval\Value;

class ArrayLimitPlugin extends Plugin
{
    /**
     * Maximum size of arrays before limiting.
     *
     * @var int
     */
    public static $trigger = 1000;

    /**
     * Maximum amount of items to show in a limited array.
     *
     * @var int
     */
    public static $limit = 50;

    /**
     * Don't limit arrays with string keys.
     *
     * @var bool
     */
    public static $numeric_only = true;

    public function getTypes()
    {
        return ['array'];
    }

    public function getTriggers()
    {
        return Parser::TRIGGER_BEGIN;
    }

    public function parse(&$var, Value &$o, $trigger)
    {
        if (self::$limit >= self::$trigger) {
            throw new InvalidArgumentException('ArrayLimitPlugin::$limit can not be lower than ArrayLimitPlugin::$trigger');
        }

        $depth = $this->parser->getDepthLimit();

        if (!$depth) {
            return;
        }

        if ($o->depth >= $depth - 1) {
            return;
        }

        if (\count($var) < self::$trigger) {
            return;
        }

        if (self::$numeric_only && Utils::isAssoc($var)) {
            return;
        }

        $base = clone $o;
        $base->depth = $depth - 1;
        $obj = $this->parser->parse($var, $base);

        if (!$obj instanceof Value || 'array' != $obj->type) {
            return; // @codeCoverageIgnore
        }

        $obj->depth = $o->depth;
        $i = 0;

        foreach ($obj->value->contents as $child) {
            // We only bother setting the correct depth for the first child,
            // any deeper children should be cancelled by the depth limit
            $child->depth = $o->depth + 1;
            $this->recalcDepthLimit($child);
        }

        $var2 = \array_slice($var, 0, self::$limit, true);
        $base = clone $o;
        $slice = $this->parser->parse($var2, $base);

        \array_splice($obj->value->contents, 0, self::$limit, $slice->value->contents);

        $o = $obj;

        $this->parser->haltParse();
    }

    protected function recalcDepthLimit(Value $o)
    {
        $hintkey = \array_search('depth_limit', $o->hints, true);
        if (false !== $hintkey) {
            $o->hints[$hintkey] = 'array_limit';
        }

        $reps = $o->getRepresentations();
        if ($o->value) {
            $reps[] = $o->value;
        }

        foreach ($reps as $rep) {
            if ($rep->contents instanceof Value) {
                $this->recalcDepthLimit($rep->contents);
            } elseif (\is_array($rep->contents)) {
                foreach ($rep->contents as $child) {
                    if ($child instanceof Value) {
                        $this->recalcDepthLimit($child);
                    }
                }
            }
        }
    }
}
