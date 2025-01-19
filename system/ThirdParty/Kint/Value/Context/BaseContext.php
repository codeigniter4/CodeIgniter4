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

namespace Kint\Value\Context;

use InvalidArgumentException;

/**
 * @psalm-import-type ValueName from ContextInterface
 */
class BaseContext implements ContextInterface
{
    /** @psalm-var ValueName */
    public $name;
    public int $depth = 0;
    public bool $reference = false;
    public ?string $access_path = null;

    /** @psalm-param mixed $name */
    public function __construct($name)
    {
        if (!\is_string($name) && !\is_int($name)) {
            throw new InvalidArgumentException('Context names must be string|int');
        }

        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDepth(): int
    {
        return $this->depth;
    }

    public function getOperator(): ?string
    {
        return null;
    }

    public function isRef(): bool
    {
        return $this->reference;
    }

    /** @psalm-param ?class-string $scope */
    public function isAccessible(?string $scope): bool
    {
        return true;
    }

    public function getAccessPath(): ?string
    {
        return $this->access_path;
    }
}
