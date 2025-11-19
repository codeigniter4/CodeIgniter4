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

use Kint\Utils;
use Kint\Value\AbstractValue;
use Kint\Value\ArrayValue;
use Kint\Value\Context\ArrayContext;
use Kint\Value\Representation\ContainerRepresentation;
use Kint\Value\Representation\SourceRepresentation;
use Kint\Value\Representation\ValueRepresentation;
use Kint\Value\TraceFrameValue;
use Kint\Value\TraceValue;
use RuntimeException;

/**
 * @psalm-import-type TraceFrame from TraceFrameValue
 */
class TracePlugin extends AbstractPlugin implements PluginCompleteInterface
{
    public static array $blacklist = ['spl_autoload_call'];
    public static array $path_blacklist = [];

    public function getTypes(): array
    {
        return ['array'];
    }

    public function getTriggers(): int
    {
        return Parser::TRIGGER_SUCCESS;
    }

    public function parseComplete(&$var, AbstractValue $v, int $trigger): AbstractValue
    {
        if (!$v instanceof ArrayValue) {
            return $v;
        }

        // Shallow copy so we don't have to worry about touching var
        $trace = $var;

        if (!Utils::isTrace($trace)) {
            return $v;
        }

        $pdepth = $this->getParser()->getDepthLimit();
        $c = $v->getContext();

        // We need at least 2 levels in order to get $trace[n]['args']
        if ($pdepth && $c->getDepth() + 2 >= $pdepth) {
            return $v;
        }

        $contents = $v->getContents();

        self::$blacklist = Utils::normalizeAliases(self::$blacklist);
        $path_blacklist = self::normalizePaths(self::$path_blacklist);

        $frames = [];

        foreach ($contents as $frame) {
            if (!$frame instanceof ArrayValue || !$frame->getContext() instanceof ArrayContext) {
                continue;
            }

            $index = $frame->getContext()->getName();

            if (!isset($trace[$index]['file']) || Utils::traceFrameIsListed($trace[$index], self::$blacklist)) {
                continue;
            }

            if (false !== ($realfile = \realpath($trace[$index]['file']))) {
                foreach ($path_blacklist as $path) {
                    if (0 === \strpos($realfile, $path)) {
                        continue 2;
                    }
                }
            }

            $frame = new TraceFrameValue($frame, $trace[$index]);

            if (null !== ($file = $frame->getFile()) && null !== ($line = $frame->getLine())) {
                try {
                    $frame->addRepresentation(new SourceRepresentation($file, $line));
                } catch (RuntimeException $e) {
                }
            }

            if ($args = $frame->getArgs()) {
                $frame->addRepresentation(new ContainerRepresentation('Arguments', $args));
            }

            if ($obj = $frame->getObject()) {
                $frame->addRepresentation(
                    new ValueRepresentation(
                        'Callee object ['.$obj->getClassName().']',
                        $obj,
                        'callee_object'
                    )
                );
            }

            $frames[$index] = $frame;
        }

        $traceobj = new TraceValue($c, \count($frames), $frames);

        if ($frames) {
            $traceobj->addRepresentation(new ContainerRepresentation('Contents', $frames, null, true));
        }

        return $traceobj;
    }

    protected static function normalizePaths(array $paths): array
    {
        $normalized = [];

        foreach ($paths as $path) {
            $realpath = \realpath($path);
            if (false !== $realpath && \is_dir($realpath)) {
                $realpath .= DIRECTORY_SEPARATOR;
            }

            $normalized[] = $realpath;
        }

        return $normalized;
    }
}
