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

use Kint\Renderer\PlainRenderer;
use Kint\Renderer\TextRenderer;
use Kint\Utils;
use Kint\Value\AbstractValue;
use Kint\Value\Representation\MicrotimeRepresentation;

class MicrotimePlugin extends AbstractPlugin
{
    protected bool $useJs = false;

    public function __construct(TextRenderer $r)
    {
        parent::__construct($r);

        if ($this->renderer instanceof PlainRenderer) {
            $this->useJs = true;
        }
    }

    public function render(AbstractValue $v): ?string
    {
        $r = $v->getRepresentation('microtime');

        if (!$r instanceof MicrotimeRepresentation || !($dt = $r->getDateTime())) {
            return null;
        }

        $c = $v->getContext();

        $out = '';

        if (0 === $c->getDepth()) {
            $out .= $this->renderer->colorTitle($this->renderer->renderTitle($v)).PHP_EOL;
        }

        $out .= $this->renderer->renderHeader($v);
        $out .= $this->renderer->renderChildren($v).PHP_EOL;

        $indent = \str_repeat(' ', ($c->getDepth() + 1) * $this->renderer->indent_width);

        if ($this->useJs) {
            $out .= '<span data-kint-microtime-group="'.$r->getGroup().'">';
        }

        $out .= $indent.$this->renderer->colorType('TIME:').' ';
        $out .= $this->renderer->colorValue($dt->format('Y-m-d H:i:s.u')).PHP_EOL;

        if (null !== ($lap = $r->getLapTime())) {
            $out .= $indent.$this->renderer->colorType('SINCE LAST CALL:').' ';

            $lap = \round($lap, 4);

            if ($this->useJs) {
                $lap = '<span class="kint-microtime-lap">'.$lap.'</span>';
            }

            $out .= $this->renderer->colorValue($lap.'s').'.'.PHP_EOL;
        }
        if (null !== ($total = $r->getTotalTime())) {
            $out .= $indent.$this->renderer->colorType('SINCE START:').' ';
            $out .= $this->renderer->colorValue(\round($total, 4).'s').'.'.PHP_EOL;
        }
        if (null !== ($avg = $r->getAverageTime())) {
            $out .= $indent.$this->renderer->colorType('AVERAGE DURATION:').' ';

            $avg = \round($avg, 4);

            if ($this->useJs) {
                $avg = '<span class="kint-microtime-avg">'.$avg.'</span>';
            }

            $out .= $this->renderer->colorValue($avg.'s').'.'.PHP_EOL;
        }

        $bytes = Utils::getHumanReadableBytes($r->getMemoryUsage());
        $mem = $r->getMemoryUsage().' bytes ('.\round($bytes['value'], 3).' '.$bytes['unit'].')';
        $bytes = Utils::getHumanReadableBytes($r->getMemoryUsageReal());
        $mem .= ' (real '.\round($bytes['value'], 3).' '.$bytes['unit'].')';

        $out .= $indent.$this->renderer->colorType('MEMORY USAGE:').' ';
        $out .= $this->renderer->colorValue($mem).'.'.PHP_EOL;

        $bytes = Utils::getHumanReadableBytes($r->getMemoryPeakUsage());
        $mem = $r->getMemoryPeakUsage().' bytes ('.\round($bytes['value'], 3).' '.$bytes['unit'].')';
        $bytes = Utils::getHumanReadableBytes($r->getMemoryPeakUsageReal());
        $mem .= ' (real '.\round($bytes['value'], 3).' '.$bytes['unit'].')';

        $out .= $indent.$this->renderer->colorType('PEAK MEMORY USAGE:').' ';
        $out .= $this->renderer->colorValue($mem).'.'.PHP_EOL;

        if ($this->useJs) {
            $out .= '</span>';
        }

        return $out;
    }
}
