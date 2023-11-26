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

use Kint\Kint;
use Kint\Zval\Representation\MethodDefinitionRepresentation;
use Kint\Zval\Representation\Representation;

class MethodDefinitionPlugin extends AbstractPlugin implements TabPluginInterface
{
    public function renderTab(Representation $r): ?string
    {
        if (!$r instanceof MethodDefinitionRepresentation) {
            return null;
        }

        if (isset($r->contents)) {
            $docstring = [];
            foreach (\explode("\n", $r->contents) as $line) {
                $docstring[] = \trim($line);
            }

            $docstring = $this->renderer->escape(\implode("\n", $docstring));
        }

        $addendum = [];
        if (isset($r->class) && $r->inherited) {
            $addendum[] = 'Inherited from '.$this->renderer->escape($r->class);
        }

        if (isset($r->file, $r->line)) {
            $addendum[] = 'Defined in '.$this->renderer->escape(Kint::shortenPath($r->file)).':'.((int) $r->line);
        }

        if ($addendum) {
            $addendum = '<small>'.\implode("\n", $addendum).'</small>';

            if (isset($docstring)) {
                $docstring .= "\n\n".$addendum;
            } else {
                $docstring = $addendum;
            }
        }

        if (!isset($docstring)) {
            return null;
        }

        return '<pre>'.$docstring.'</pre>';
    }
}
