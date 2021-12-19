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

use Kint\Utils;
use Kint\Zval\Representation\MicrotimeRepresentation;
use Kint\Zval\Representation\Representation;

class MicrotimePlugin extends Plugin implements TabPluginInterface
{
    public function renderTab(Representation $r)
    {
        if (!($r instanceof MicrotimeRepresentation)) {
            return;
        }

        $out = $r->getDateTime()->format('Y-m-d H:i:s.u');
        if (null !== $r->lap) {
            $out .= '<br><b>SINCE LAST CALL:</b> <span class="kint-microtime-lap">'.\round($r->lap, 4).'</span>s.';
        }
        if (null !== $r->total) {
            $out .= '<br><b>SINCE START:</b> '.\round($r->total, 4).'s.';
        }
        if (null !== $r->avg) {
            $out .= '<br><b>AVERAGE DURATION:</b> <span class="kint-microtime-avg">'.\round($r->avg, 4).'</span>s.';
        }

        $bytes = Utils::getHumanReadableBytes($r->mem);
        $out .= '<br><b>MEMORY USAGE:</b> '.$r->mem.' bytes ('.\round($bytes['value'], 3).' '.$bytes['unit'].')';
        $bytes = Utils::getHumanReadableBytes($r->mem_real);
        $out .= ' (real '.\round($bytes['value'], 3).' '.$bytes['unit'].')';

        $bytes = Utils::getHumanReadableBytes($r->mem_peak);
        $out .= '<br><b>PEAK MEMORY USAGE:</b> '.$r->mem_peak.' bytes ('.\round($bytes['value'], 3).' '.$bytes['unit'].')';
        $bytes = Utils::getHumanReadableBytes($r->mem_peak_real);
        $out .= ' (real '.\round($bytes['value'], 3).' '.$bytes['unit'].')';

        return '<pre data-kint-microtime-group="'.$r->group.'">'.$out.'</pre>';
    }

    public static function renderJs()
    {
        return \file_get_contents(KINT_DIR.'/resources/compiled/microtime.js');
    }
}
