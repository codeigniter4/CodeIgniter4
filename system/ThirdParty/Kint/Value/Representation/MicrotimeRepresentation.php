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

namespace Kint\Value\Representation;

use DateTimeImmutable;
use DateTimeInterface;

class MicrotimeRepresentation extends AbstractRepresentation
{
    /** @psalm-readonly */
    protected int $seconds;
    /** @psalm-readonly */
    protected int $microseconds;
    /** @psalm-readonly */
    protected string $group;
    /** @psalm-readonly */
    protected ?float $lap_time;
    /** @psalm-readonly */
    protected ?float $total_time;
    protected ?float $avg_time = null;
    /** @psalm-readonly */
    protected int $mem;
    /** @psalm-readonly */
    protected int $mem_real;
    /** @psalm-readonly */
    protected int $mem_peak;
    /** @psalm-readonly */
    protected int $mem_peak_real;

    public function __construct(int $seconds, int $microseconds, string $group, ?float $lap_time = null, ?float $total_time = null, int $i = 0)
    {
        parent::__construct('Microtime', null, true);

        $this->seconds = $seconds;
        $this->microseconds = $microseconds;

        $this->group = $group;
        $this->lap_time = $lap_time;
        $this->total_time = $total_time;

        if ($i > 0) {
            $this->avg_time = $total_time / $i;
        }

        $this->mem = \memory_get_usage();
        $this->mem_real = \memory_get_usage(true);
        $this->mem_peak = \memory_get_peak_usage();
        $this->mem_peak_real = \memory_get_peak_usage(true);
    }

    public function getHint(): string
    {
        return 'microtime';
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    public function getLapTime(): ?float
    {
        return $this->lap_time;
    }

    public function getTotalTime(): ?float
    {
        return $this->total_time;
    }

    public function getAverageTime(): ?float
    {
        return $this->avg_time;
    }

    public function getMemoryUsage(): int
    {
        return $this->mem;
    }

    public function getMemoryUsageReal(): int
    {
        return $this->mem_real;
    }

    public function getMemoryPeakUsage(): int
    {
        return $this->mem_peak;
    }

    public function getMemoryPeakUsageReal(): int
    {
        return $this->mem_peak_real;
    }

    public function getDateTime(): ?DateTimeInterface
    {
        return DateTimeImmutable::createFromFormat('U u', $this->seconds.' '.\str_pad((string) $this->microseconds, 6, '0', STR_PAD_LEFT)) ?: null;
    }
}
