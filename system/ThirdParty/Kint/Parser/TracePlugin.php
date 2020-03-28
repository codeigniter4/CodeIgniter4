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

use Kint\Object\BasicObject;
use Kint\Object\TraceFrameObject;
use Kint\Object\TraceObject;
use Kint\Utils;

class TracePlugin extends Plugin
{
    public static $blacklist = array('spl_autoload_call');

    public function getTypes()
    {
        return array('array');
    }

    public function getTriggers()
    {
        return Parser::TRIGGER_SUCCESS;
    }

    public function parse(&$var, BasicObject &$o, $trigger)
    {
        if (!$o->value) {
            return;
        }

        $trace = $this->parser->getCleanArray($var);

        if (\count($trace) !== \count($o->value->contents) || !Utils::isTrace($trace)) {
            return;
        }

        $traceobj = new TraceObject();
        $traceobj->transplant($o);
        $rep = $traceobj->value;

        $old_trace = $rep->contents;

        Utils::normalizeAliases(self::$blacklist);

        $rep->contents = array();

        foreach ($old_trace as $frame) {
            $index = $frame->name;

            if (!isset($trace[$index]['function'])) {
                // Something's very very wrong here, but it's probably a plugin's fault
                continue;
            }

            if (Utils::traceFrameIsListed($trace[$index], self::$blacklist)) {
                continue;
            }

            $rep->contents[$index] = new TraceFrameObject($frame, $trace[$index]);
        }

        \ksort($rep->contents);
        $rep->contents = \array_values($rep->contents);

        $traceobj->clearRepresentations();
        $traceobj->addRepresentation($rep);
        $traceobj->size = \count($rep->contents);
        $o = $traceobj;
    }
}
