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
use Kint\Value\Representation\RepresentationInterface;
use Kint\Value\Representation\SourceRepresentation;

class SourcePlugin extends AbstractPlugin implements TabPluginInterface
{
    public function renderTab(RepresentationInterface $r, AbstractValue $v): ?string
    {
        if (!$r instanceof SourceRepresentation) {
            return null;
        }

        $source = $r->getSourceLines();

        // Trim empty lines from the start and end of the source
        foreach ($source as $linenum => $line) {
            if (\strlen(\trim($line)) || $linenum === $r->getLine()) {
                break;
            }

            unset($source[$linenum]);
        }

        foreach (\array_reverse($source, true) as $linenum => $line) {
            if (\strlen(\trim($line)) || $linenum === $r->getLine()) {
                break;
            }

            unset($source[$linenum]);
        }

        $output = '';

        foreach ($source as $linenum => $line) {
            if ($linenum === $r->getLine()) {
                $output .= '<div class="kint-highlight">'.$this->renderer->escape($line)."\n".'</div>';
            } else {
                $output .= '<div>'.$this->renderer->escape($line)."\n".'</div>';
            }
        }

        if ($output) {
            $data = '';
            if ($r->showFileName()) {
                $data = ' data-kint-filename="'.$this->renderer->escape($r->getFileName()).'"';
            }

            return '<div><pre class="kint-source"'.$data.' style="counter-reset: kint-l '.((int) \array_key_first($source) - 1).';">'.$output.'</pre></div><div></div>';
        }

        return null;
    }
}
