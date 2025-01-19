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

use Kint\Renderer\RichRenderer;
use Kint\Value\AbstractValue;
use Kint\Value\Context\ClassDeclaredContext;
use Kint\Value\Context\PropertyContext;
use Kint\Value\InstanceValue;

abstract class AbstractPlugin implements PluginInterface
{
    protected RichRenderer $renderer;

    public function __construct(RichRenderer $r)
    {
        $this->renderer = $r;
    }

    /**
     * @param string $content The replacement for the getValueShort contents
     */
    public function renderLockedHeader(AbstractValue $v, string $content): string
    {
        $header = '<dt class="kint-parent kint-locked">';

        $c = $v->getContext();

        if (RichRenderer::$access_paths && $c->getDepth() > 0 && null !== ($ap = $c->getAccessPath())) {
            $header .= '<span class="kint-access-path-trigger" title="Show access path">&rlarr;</span>';
        }

        $header .= '<nav></nav>';

        if ($c instanceof ClassDeclaredContext) {
            $header .= '<var>'.$c->getModifiers().'</var> ';
        }

        $header .= '<dfn>'.$this->renderer->escape($v->getDisplayName()).'</dfn> ';

        if ($c instanceof PropertyContext && null !== ($s = $c->getHooks())) {
            $header .= '<var>'.$this->renderer->escape($s).'</var> ';
        }

        if (null !== ($s = $c->getOperator())) {
            $header .= $this->renderer->escape($s, 'ASCII').' ';
        }

        $s = $v->getDisplayType();

        if (RichRenderer::$escape_types) {
            $s = $this->renderer->escape($s);
        }

        if ($c->isRef()) {
            $s = '&amp;'.$s;
        }

        $header .= '<var>'.$s.'</var>';

        if ($v instanceof InstanceValue && $this->renderer->shouldRenderObjectIds()) {
            $header .= '#'.$v->getSplObjectId();
        }

        $header .= ' ';

        if (null !== ($s = $v->getDisplaySize())) {
            if (RichRenderer::$escape_types) {
                $s = $this->renderer->escape($s);
            }
            $header .= '('.$s.') ';
        }

        $header .= $content;

        if (!empty($ap)) {
            $header .= '<div class="access-path">'.$this->renderer->escape($ap).'</div>';
        }

        return $header.'</dt>';
    }
}
