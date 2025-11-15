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

namespace Kint\Renderer\Text;

use Kint\Value\AbstractValue;
use Kint\Value\MethodValue;
use Kint\Value\Representation\SourceRepresentation;
use Kint\Value\TraceFrameValue;
use Kint\Value\TraceValue;

class TracePlugin extends AbstractPlugin
{
    public function render(AbstractValue $v): ?string
    {
        if (!$v instanceof TraceValue) {
            return null;
        }

        $c = $v->getContext();

        $out = '';

        if (0 === $c->getDepth()) {
            $out .= $this->renderer->colorTitle($this->renderer->renderTitle($v)).PHP_EOL;
        }

        $out .= $this->renderer->renderHeader($v).':'.PHP_EOL;

        $indent = \str_repeat(' ', ($c->getDepth() + 1) * $this->renderer->indent_width);

        $i = 1;
        foreach ($v->getContents() as $frame) {
            if (!$frame instanceof TraceFrameValue) {
                continue;
            }

            $framedesc = $indent.\str_pad($i.': ', 4, ' ');

            if (null !== ($file = $frame->getFile()) && null !== ($line = $frame->getLine())) {
                $framedesc .= $this->renderer->ideLink($file, $line).PHP_EOL;
            } else {
                $framedesc .= 'PHP internal call'.PHP_EOL;
            }

            if ($callable = $frame->getCallable()) {
                $framedesc .= $indent.'    ';

                if ($callable instanceof MethodValue) {
                    $framedesc .= $this->renderer->escape($callable->getContext()->owner_class.$callable->getContext()->getOperator());
                }

                $framedesc .= $this->renderer->escape($callable->getDisplayName());
            }

            $out .= $this->renderer->colorType($framedesc).PHP_EOL.PHP_EOL;

            $source = $frame->getRepresentation('source');

            if ($source instanceof SourceRepresentation) {
                $line_wanted = $source->getLine();
                $source = $source->getSourceLines();

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
                    if ($lineno === $line_wanted) {
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
