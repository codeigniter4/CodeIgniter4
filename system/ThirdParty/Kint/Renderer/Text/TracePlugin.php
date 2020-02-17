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

namespace Kint\Renderer\Text;

use Kint\Object\BasicObject;
use Kint\Object\MethodObject;

class TracePlugin extends Plugin
{
    public function render(BasicObject $o)
    {
        $out = '';

        if (0 == $o->depth) {
            $out .= $this->renderer->colorTitle($this->renderer->renderTitle($o)).PHP_EOL;
        }

        $out .= $this->renderer->renderHeader($o).':'.PHP_EOL;

        $indent = \str_repeat(' ', ($o->depth + 1) * $this->renderer->indent_width);

        $i = 1;
        foreach ($o->value->contents as $frame) {
            $framedesc = $indent.\str_pad($i.': ', 4, ' ');

            if ($frame->trace['file']) {
                $framedesc .= $this->renderer->ideLink($frame->trace['file'], $frame->trace['line']).PHP_EOL;
            } else {
                $framedesc .= 'PHP internal call'.PHP_EOL;
            }

            $framedesc .= $indent.'    ';

            if ($frame->trace['class']) {
                $framedesc .= $this->renderer->escape($frame->trace['class']);

                if ($frame->trace['object']) {
                    $framedesc .= $this->renderer->escape('->');
                } else {
                    $framedesc .= '::';
                }
            }

            if (\is_string($frame->trace['function'])) {
                $framedesc .= $this->renderer->escape($frame->trace['function']).'(...)';
            } elseif ($frame->trace['function'] instanceof MethodObject) {
                $framedesc .= $this->renderer->escape($frame->trace['function']->getName());
                $framedesc .= '('.$this->renderer->escape($frame->trace['function']->getParams()).')';
            }

            $out .= $this->renderer->colorType($framedesc).PHP_EOL.PHP_EOL;

            if ($source = $frame->getRepresentation('source')) {
                $line_wanted = $source->line;
                $source = $source->source;

                // Trim empty lines from the start and end of the source
                foreach ($source as $linenum => $line) {
                    if (\trim($line) || $linenum === $line_wanted) {
                        break;
                    }

                    unset($source[$linenum]);
                }

                foreach (\array_reverse($source, true) as $linenum => $line) {
                    if (\trim($line) || $linenum === $line_wanted) {
                        break;
                    }

                    unset($source[$linenum]);
                }

                foreach ($source as $lineno => $line) {
                    if ($lineno == $line_wanted) {
                        $out .= $indent.$this->renderer->colorValue($this->renderer->escape($line)).PHP_EOL;
                    } else {
                        $out .= $indent.$this->renderer->escape($line).PHP_EOL;
                    }
                }
            }

            ++$i;
        }

        return $out;
    }
}
