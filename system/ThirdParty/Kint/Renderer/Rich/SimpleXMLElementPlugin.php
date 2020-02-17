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

use Kint\Object\BasicObject;
use Kint\Object\BlobObject;
use Kint\Renderer\RichRenderer;

class SimpleXMLElementPlugin extends Plugin implements ObjectPluginInterface
{
    public function renderObject(BasicObject $o)
    {
        $children = $this->renderer->renderChildren($o);

        $header = '';

        if (null !== ($s = $o->getModifiers())) {
            $header .= '<var>'.$s.'</var> ';
        }

        if (null !== ($s = $o->getName())) {
            $header .= '<dfn>'.$this->renderer->escape($s).'</dfn> ';

            if ($s = $o->getOperator()) {
                $header .= $this->renderer->escape($s, 'ASCII').' ';
            }
        }

        if (null !== ($s = $o->getType())) {
            $s = $this->renderer->escape($s);

            if ($o->reference) {
                $s = '&amp;'.$s;
            }

            $header .= '<var>'.$this->renderer->escape($s).'</var> ';
        }

        if (null !== ($s = $o->getSize())) {
            $header .= '('.$this->renderer->escape($s).') ';
        }

        if (null === $s && $c = $o->getRepresentation('contents')) {
            $c = \reset($c->contents);

            if ($c && null !== ($s = $c->getValueShort())) {
                if (RichRenderer::$strlen_max && BlobObject::strlen($s) > RichRenderer::$strlen_max) {
                    $s = \substr($s, 0, RichRenderer::$strlen_max).'...';
                }
                $header .= $this->renderer->escape($s);
            }
        }

        $header = $this->renderer->renderHeaderWrapper($o, (bool) \strlen($children), $header);

        return '<dl>'.$header.$children.'</dl>';
    }
}
