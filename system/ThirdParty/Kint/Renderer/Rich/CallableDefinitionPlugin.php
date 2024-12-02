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

use Kint\Utils;
use Kint\Value\AbstractValue;
use Kint\Value\MethodValue;
use Kint\Value\Representation\CallableDefinitionRepresentation;
use Kint\Value\Representation\RepresentationInterface;

class CallableDefinitionPlugin extends AbstractPlugin implements TabPluginInterface
{
    public function renderTab(RepresentationInterface $r, AbstractValue $v): ?string
    {
        if (!$r instanceof CallableDefinitionRepresentation) {
            return null;
        }

        $docstring = [];

        if ($v instanceof MethodValue) {
            $c = $v->getContext();

            if ($c->inherited) {
                $docstring[] = 'Inherited from '.$this->renderer->escape($c->owner_class);
            }
        }

        $docstring[] = 'Defined in '.$this->renderer->escape(Utils::shortenPath($r->getFileName())).':'.$r->getLine();

        $docstring = '<small>'.\implode("\n", $docstring).'</small>';

        if (null !== ($trimmed = $r->getDocstringTrimmed())) {
            $docstring = $this->renderer->escape($trimmed)."\n\n".$docstring;
        }

        return '<pre>'.$docstring.'</pre>';
    }
}
