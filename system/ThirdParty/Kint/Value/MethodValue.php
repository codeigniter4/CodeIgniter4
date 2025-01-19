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

use Kint\Value\Context\ClassDeclaredContext;
use Kint\Value\Context\MethodContext;
use Kint\Value\Representation\CallableDefinitionRepresentation;

class MethodValue extends AbstractValue
{
    /** @psalm-readonly */
    protected DeclaredCallableBag $callable_bag;
    /** @psalm-readonly */
    protected ?CallableDefinitionRepresentation $definition_rep;

    public function __construct(MethodContext $c, DeclaredCallableBag $bag)
    {
        parent::__construct($c, 'method');

        $this->callable_bag = $bag;

        if ($this->callable_bag->internal) {
            $this->definition_rep = null;

            return;
        }

        /**
         * @psalm-var string $this->callable_bag->filename
         * @psalm-var int $this->callable_bag->startline
         * Psalm issue #11121
         */
        $this->definition_rep = new CallableDefinitionRepresentation(
            $this->callable_bag->filename,
            $this->callable_bag->startline,
            $this->callable_bag->docstring
        );
        $this->addRepresentation($this->definition_rep);
    }

    public function getHint(): string
    {
        return parent::getHint() ?? 'callable';
    }

    public function getContext(): MethodContext
    {
        /**
         * @psalm-var MethodContext $this->context
         * Psalm discuss #11116
         */
        return $this->context;
    }

    public function getCallableBag(): DeclaredCallableBag
    {
        return $this->callable_bag;
    }

    /** @psalm-api */
    public function getDefinitionRepresentation(): ?CallableDefinitionRepresentation
    {
        return $this->definition_rep;
    }

    public function getFullyQualifiedDisplayName(): string
    {
        $c = $this->getContext();

        return $c->owner_class.'::'.$c->getName().'('.$this->callable_bag->getParams().')';
    }

    public function getDisplayName(): string
    {
        $c = $this->getContext();

        if ($c->static || (ClassDeclaredContext::ACCESS_PRIVATE === $c->access && $c->inherited)) {
            return $this->getFullyQualifiedDisplayName();
        }

        return $c->getName().'('.$this->callable_bag->getParams().')';
    }

    public function getDisplayValue(): ?string
    {
        if ($this->definition_rep instanceof CallableDefinitionRepresentation) {
            return $this->definition_rep->getDocstringFirstLine();
        }

        return parent::getDisplayValue();
    }

    public function getPhpDocUrl(): ?string
    {
        if (!$this->callable_bag->internal) {
            return null;
        }

        $c = $this->getContext();

        $class = \str_replace('\\', '-', \strtolower($c->owner_class));
        $funcname = \str_replace('_', '-', \strtolower($c->getName()));

        if (0 === \strpos($funcname, '--') && 0 !== \strpos($funcname, '-', 2)) {
            $funcname = \substr($funcname, 2);
        }

        return 'https://www.php.net/'.$class.'.'.$funcname;
    }
}
