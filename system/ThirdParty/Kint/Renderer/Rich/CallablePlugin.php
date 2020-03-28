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
use Kint\Object\ClosureObject;
use Kint\Object\MethodObject;
use Kint\Renderer\RichRenderer;

class CallablePlugin extends Plugin implements ObjectPluginInterface
{
    protected static $method_cache = array();

    public function renderObject(BasicObject $o)
    {
        if ($o instanceof MethodObject) {
            return $this->renderMethod($o);
        }

        if ($o instanceof ClosureObject) {
            return $this->renderClosure($o);
        }

        return $this->renderCallable($o);
    }

    protected function renderClosure(ClosureObject $o)
    {
        $children = $this->renderer->renderChildren($o);

        $header = '';

        if (null !== ($s = $o->getModifiers())) {
            $header .= '<var>'.$s.'</var> ';
        }

        if (null !== ($s = $o->getName())) {
            $header .= '<dfn>'.$this->renderer->escape($s).'('.$this->renderer->escape($o->getParams()).')</dfn>';
        }

        if (null !== ($s = $o->getValueShort())) {
            if (RichRenderer::$strlen_max && BlobObject::strlen($s) > RichRenderer::$strlen_max) {
                $s = \substr($s, 0, RichRenderer::$strlen_max).'...';
            }
            $header .= ' '.$this->renderer->escape($s);
        }

        return '<dl>'.$this->renderer->renderHeaderWrapper($o, (bool) \strlen($children), $header).$children.'</dl>';
    }

    protected function renderCallable(BasicObject $o)
    {
        $children = $this->renderer->renderChildren($o);

        $header = '';

        if (null !== ($s = $o->getModifiers())) {
            $header .= '<var>'.$s.'</var> ';
        }

        if (null !== ($s = $o->getName())) {
            $header .= '<dfn>'.$this->renderer->escape($s).'</dfn>';
        }

        if (null !== ($s = $o->getValueShort())) {
            if (RichRenderer::$strlen_max && BlobObject::strlen($s) > RichRenderer::$strlen_max) {
                $s = \substr($s, 0, RichRenderer::$strlen_max).'...';
            }
            $header .= ' '.$this->renderer->escape($s);
        }

        return '<dl>'.$this->renderer->renderHeaderWrapper($o, (bool) \strlen($children), $header).$children.'</dl>';
    }

    protected function renderMethod(MethodObject $o)
    {
        if (!empty(self::$method_cache[$o->owner_class][$o->name])) {
            $children = self::$method_cache[$o->owner_class][$o->name]['children'];

            $header = $this->renderer->renderHeaderWrapper(
                $o,
                (bool) \strlen($children),
                self::$method_cache[$o->owner_class][$o->name]['header']
            );

            return '<dl>'.$header.$children.'</dl>';
        }

        $children = $this->renderer->renderChildren($o);

        $header = '';

        if (null !== ($s = $o->getModifiers()) || $o->return_reference) {
            $header .= '<var>'.$s;

            if ($o->return_reference) {
                if ($s) {
                    $header .= ' ';
                }
                $header .= $this->renderer->escape('&');
            }

            $header .= '</var> ';
        }

        if (null !== ($s = $o->getName())) {
            $function = $this->renderer->escape($s).'('.$this->renderer->escape($o->getParams()).')';

            if (null !== ($url = $o->getPhpDocUrl())) {
                $function = '<a href="'.$url.'" target=_blank>'.$function.'</a>';
            }

            $header .= '<dfn>'.$function.'</dfn>';
        }

        if (!empty($o->returntype)) {
            $header .= ': <var>';

            if ($o->return_reference) {
                $header .= $this->renderer->escape('&');
            }

            $header .= $this->renderer->escape($o->returntype).'</var>';
        } elseif ($o->docstring) {
            if (\preg_match('/@return\\s+(.*)\\r?\\n/m', $o->docstring, $matches)) {
                if (\trim($matches[1])) {
                    $header .= ': <var>'.$this->renderer->escape(\trim($matches[1])).'</var>';
                }
            }
        }

        if (null !== ($s = $o->getValueShort())) {
            if (RichRenderer::$strlen_max && BlobObject::strlen($s) > RichRenderer::$strlen_max) {
                $s = \substr($s, 0, RichRenderer::$strlen_max).'...';
            }
            $header .= ' '.$this->renderer->escape($s);
        }

        if (\strlen($o->owner_class) && \strlen($o->name)) {
            self::$method_cache[$o->owner_class][$o->name] = array(
                'header' => $header,
                'children' => $children,
            );
        }

        $header = $this->renderer->renderHeaderWrapper($o, (bool) \strlen($children), $header);

        return '<dl>'.$header.$children.'</dl>';
    }
}
