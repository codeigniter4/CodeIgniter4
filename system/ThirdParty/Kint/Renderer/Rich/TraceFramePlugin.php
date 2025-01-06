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

namespace Kint\Renderer\Rich;

use Kint\Value\AbstractValue;
use Kint\Value\MethodValue;
use Kint\Value\TraceFrameValue;

class TraceFramePlugin extends AbstractPlugin implements ValuePluginInterface
{
    public function renderValue(AbstractValue $v): ?string
    {
        if (!$v instanceof TraceFrameValue) {
            return null;
        }

        if (null !== ($file = $v->getFile()) && null !== ($line = $v->getLine())) {
            $header = '<var>'.$this->renderer->ideLink($file, $line).'</var> ';
        } else {
            $header = '<var>PHP internal call</var> ';
        }

        if ($callable = $v->getCallable()) {
            if ($callable instanceof MethodValue) {
                $function = $callable->getFullyQualifiedDisplayName();
            } else {
                $function = $callable->getDisplayName();
            }

            $function = $this->renderer->escape($function);

            if (null !== ($url = $callable->getPhpDocUrl())) {
                $function = '<a href="'.$url.'" target=_blank>'.$function.'</a>';
            }

            $header .= $function;
        }

        $children = $this->renderer->renderChildren($v);
        $header = $this->renderer->renderHeaderWrapper($v->getContext(), (bool) \strlen($children), $header);

        return '<dl>'.$header.$children.'</dl>';
    }
}
