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

use DomainException;
use Kint\Value\Context\ContextInterface;

/**
 * @psalm-type Encoding = string|false
 */
class StringValue extends AbstractValue
{
    /** @psalm-readonly */
    protected string $value;

    /**
     * @psalm-readonly
     *
     * @psalm-var Encoding
     */
    protected $encoding;

    /** @psalm-readonly */
    protected int $length;

    /** @psalm-param Encoding $encoding */
    public function __construct(ContextInterface $context, string $value, $encoding = false)
    {
        parent::__construct($context, 'string');
        $this->value = $value;
        $this->encoding = $encoding;
        $this->length = \strlen($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getValueUtf8(): string
    {
        if (false === $this->encoding) {
            throw new DomainException('StringValue with no encoding can\'t be converted to UTF-8');
        }

        if ('ASCII' === $this->encoding || 'UTF-8' === $this->encoding) {
            return $this->value;
        }

        /** @psalm-var string $encoded */
        $encoded = \mb_convert_encoding($this->value, 'UTF-8', $this->encoding);

        return $encoded;
    }

    /** @psalm-api */
    public function getLength(): int
    {
        return $this->length;
    }

    /** @psalm-return Encoding */
    public function getEncoding()
    {
        return $this->encoding;
    }

    public function getDisplayType(): string
    {
        if (false === $this->encoding) {
            return 'binary '.$this->type;
        }

        if ('ASCII' === $this->encoding) {
            return $this->type;
        }

        return $this->encoding.' '.$this->type;
    }

    public function getDisplaySize(): string
    {
        return (string) $this->length;
    }

    public function getDisplayValue(): string
    {
        return '"'.$this->value.'"';
    }
}
