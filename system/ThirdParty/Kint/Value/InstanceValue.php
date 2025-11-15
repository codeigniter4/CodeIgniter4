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

class InstanceValue extends AbstractValue
{
    /**
     * @psalm-readonly
     *
     * @psalm-var class-string
     */
    protected string $classname;
    /** @psalm-readonly */
    protected string $spl_object_hash;
    /** @psalm-readonly */
    protected int $spl_object_id;

    /**
     * The canonical children of this value, for text renderers.
     *
     * @psalm-var null|list<AbstractValue>
     */
    protected ?array $children = null;

    /** @psalm-param class-string $classname */
    public function __construct(
        ContextInterface $context,
        string $classname,
        string $spl_object_hash,
        int $spl_object_id
    ) {
        parent::__construct($context, 'object');
        $this->classname = $classname;
        $this->spl_object_hash = $spl_object_hash;
        $this->spl_object_id = $spl_object_id;
    }

    /** @psalm-return class-string */
    public function getClassName(): string
    {
        return $this->classname;
    }

    public function getSplObjectHash(): string
    {
        return $this->spl_object_hash;
    }

    public function getSplObjectId(): int
    {
        return $this->spl_object_id;
    }

    /** @psalm-param null|list<AbstractValue> $children */
    public function setChildren(?array $children): void
    {
        $this->children = $children;
    }

    /** @psalm-return null|list<AbstractValue> */
    public function getChildren(): ?array
    {
        return $this->children;
    }

    public function getDisplayType(): string
    {
        return $this->classname;
    }

    public function getDisplaySize(): ?string
    {
        if (null === $this->children) {
            return null;
        }

        return (string) \count($this->children);
    }

    public function getDisplayChildren(): array
    {
        return $this->children ?? [];
    }
}
