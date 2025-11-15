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

namespace Kint\Value;

use Kint\Value\Context\ContextInterface;

class ArrayValue extends AbstractValue
{
    /** @psalm-readonly */
    protected int $size;
    /**
     * @psalm-readonly
     *
     * @psalm-var AbstractValue[]
     */
    protected array $contents;

    /** @psalm-param AbstractValue[] $contents */
    public function __construct(ContextInterface $context, int $size, array $contents)
    {
        parent::__construct($context, 'array');
        $this->size = $size;
        $this->contents = $contents;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    /** @psalm-return AbstractValue[] */
    public function getContents()
    {
        return $this->contents;
    }

    public function getDisplaySize(): string
    {
        return (string) $this->size;
    }

    public function getDisplayChildren(): array
    {
        return $this->contents;
    }
}
