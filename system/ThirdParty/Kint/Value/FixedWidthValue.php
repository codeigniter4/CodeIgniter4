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

use InvalidArgumentException;
use Kint\Value\Context\ContextInterface;

/**
 * @psalm-type FixedWidthType = null|boolean|integer|double
 */
class FixedWidthValue extends AbstractValue
{
    /**
     * @psalm-readonly
     *
     * @psalm-var FixedWidthType
     */
    protected $value;

    /** @psalm-param FixedWidthType $value */
    public function __construct(ContextInterface $context, $value)
    {
        $type = \strtolower(\gettype($value));

        if ('null' === $type || 'boolean' === $type || 'integer' === $type || 'double' === $type) {
            parent::__construct($context, $type);
            $this->value = $value;
        } else {
            throw new InvalidArgumentException('FixedWidthValue can only contain fixed width types, got '.$type);
        }
    }

    /**
     * @psalm-api
     *
     * @psalm-return FixedWidthType
     */
    public function getValue()
    {
        return $this->value;
    }

    public function getDisplaySize(): ?string
    {
        return null;
    }

    public function getDisplayValue(): ?string
    {
        if ('boolean' === $this->type) {
            return ((bool) $this->value) ? 'true' : 'false';
        }

        if ('integer' === $this->type || 'double' === $this->type) {
            return (string) $this->value;
        }

        return null;
    }
}
