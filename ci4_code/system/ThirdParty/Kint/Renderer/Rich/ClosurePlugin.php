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

namespace Kint\Renderer\Rich;

use Kint\Kint;
use Kint\Object\BasicObject;
use Kint\Object\ClosureObject;

class ClosurePlugin extends Plugin implements ObjectPluginInterface
{
    public function renderObject(BasicObject $o)
    {
        $children = $this->renderer->renderChildren($o);

        if (!($o instanceof ClosureObject)) {
            $header = $this->renderer->renderHeader($o);
        } else {
            $header = '';

            if (null !== ($s = $o->getModifiers())) {
                $header .= '<var>'.$s.'</var> ';
            }

            if (null !== ($s = $o->getName())) {
                $header .= '<dfn>'.$this->renderer->escape($s).'('.$this->renderer->escape($o->getParams()).')</dfn> ';
            }

            $header .= '<var>Closure</var> ';
            $header .= $this->renderer->escape(Kint::shortenPath($o->filename)).':'.(int) $o->startline;
        }

        $header = $this->renderer->renderHeaderWrapper($o, (bool) \strlen($children), $header);

        return '<dl>'.$header.$children.'</dl>';
    }
}
