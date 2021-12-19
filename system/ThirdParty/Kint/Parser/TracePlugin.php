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

use Kint\Utils;
use Kint\Zval\TraceFrameValue;
use Kint\Zval\TraceValue;
use Kint\Zval\Value;

class TracePlugin extends Plugin
{
    public static $blacklist = ['spl_autoload_call'];
    public static $path_blacklist = [];

    public function getTypes()
    {
        return ['array'];
    }

    public function getTriggers()
    {
        return Parser::TRIGGER_SUCCESS;
    }

    public function parse(&$var, Value &$o, $trigger)
    {
        if (!$o->value) {
            return;
        }

        /** @var array[] $trace Psalm workaround */
        $trace = $this->parser->getCleanArray($var);

        if (\count($trace) !== \count($o->value->contents) || !Utils::isTrace($trace)) {
            return;
        }

        $traceobj = new TraceValue();
        $traceobj->transplant($o);
        $rep = $traceobj->value;

        $old_trace = $rep->contents;

        Utils::normalizeAliases(self::$blacklist);
        $path_blacklist = self::normalizePaths(self::$path_blacklist);

        $rep->contents = [];

        foreach ($old_trace as $frame) {
            $index = $frame->name;

            if (!isset($trace[$index]['function'])) {
                // Something's very very wrong here, but it's probably a plugin's fault
                continue;
            }

            if (Utils::traceFrameIsListed($trace[$index], self::$blacklist)) {
                continue;
            }

            if (isset($trace[$index]['file'])) {
                $realfile = \realpath($trace[$index]['file']);
                foreach ($path_blacklist as $path) {
                    if (0 === \strpos($realfile, $path)) {
                        continue 2;
                    }
                }
            }

            $rep->contents[$index] = new TraceFrameValue($frame, $trace[$index]);
        }

        \ksort($rep->contents);
        $rep->contents = \array_values($rep->contents);

        $traceobj->clearRepresentations();
        $traceobj->addRepresentation($rep);
        $traceobj->size = \count($rep->contents);
        $o = $traceobj;
    }

    protected static function normalizePaths(array $paths)
    {
        $normalized = [];

        foreach ($paths as $path) {
            $realpath = \realpath($path);
            if (\is_dir($realpath)) {
                $realpath .= DIRECTORY_SEPARATOR;
            }

            $normalized[] = $realpath;
        }

        return $normalized;
    }
}
