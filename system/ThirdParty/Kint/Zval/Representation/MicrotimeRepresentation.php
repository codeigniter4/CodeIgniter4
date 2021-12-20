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

namespace Kint\Zval\Representation;

use DateTime;

class MicrotimeRepresentation extends Representation
{
    public $seconds;
    public $microseconds;
    public $group;
    public $lap;
    public $total;
    public $avg;
    public $i = 0;
    public $mem = 0;
    public $mem_real = 0;
    public $mem_peak = 0;
    public $mem_peak_real = 0;
    public $hints = ['microtime'];

    public function __construct($seconds, $microseconds, $group, $lap = null, $total = null, $i = 0)
    {
        parent::__construct('Microtime');

        $this->seconds = (int) $seconds;
        $this->microseconds = (int) $microseconds;

        $this->group = $group;
        $this->lap = $lap;
        $this->total = $total;
        $this->i = $i;

        if ($i) {
            $this->avg = $total / $i;
        }

        $this->mem = \memory_get_usage();
        $this->mem_real = \memory_get_usage(true);
        $this->mem_peak = \memory_get_peak_usage();
        $this->mem_peak_real = \memory_get_peak_usage(true);
    }

    public function getDateTime()
    {
        return DateTime::createFromFormat('U u', $this->seconds.' '.\str_pad($this->microseconds, 6, '0', STR_PAD_LEFT));
    }
}
