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

use Kint\Value\InstanceValue;
use ReflectionMethod;

class MethodContext extends ClassDeclaredContext
{
    public const MAGIC_NAMES = [
        '__construct' => true,
        '__destruct' => true,
        '__call' => true,
        '__callstatic' => true,
        '__get' => true,
        '__set' => true,
        '__isset' => true,
        '__unset' => true,
        '__sleep' => true,
        '__wakeup' => true,
        '__serialize' => true,
        '__unserialize' => true,
        '__tostring' => true,
        '__invoke' => true,
        '__set_state' => true,
        '__clone' => true,
        '__debuginfo' => true,
    ];

    public bool $final = false;
    public bool $abstract = false;
    public bool $static = false;

    /**
     * Whether the method was inherited from a parent class or interface.
     *
     * It's important to note that we never show static methods as
     * "inherited" except when abstract via an interface.
     */
    public bool $inherited = false;

    public function __construct(ReflectionMethod $method)
    {
        parent::__construct(
            $method->getName(),
            $method->getDeclaringClass()->name,
            ClassDeclaredContext::ACCESS_PUBLIC
        );
        $this->depth = 1;
        $this->static = $method->isStatic();
        $this->abstract = $method->isAbstract();
        $this->final = $method->isFinal();
        if ($method->isProtected()) {
            $this->access = ClassDeclaredContext::ACCESS_PROTECTED;
        } elseif ($method->isPrivate()) {
            $this->access = ClassDeclaredContext::ACCESS_PRIVATE;
        }
    }

    public function getOperator(): string
    {
        if ($this->static) {
            return '::';
        }

        return '->';
    }

    public function getModifiers(): string
    {
        if ($this->abstract) {
            $out = 'abstract ';
        } elseif ($this->final) {
            $out = 'final ';
        } else {
            $out = '';
        }

        $out .= $this->getAccess();

        if ($this->static) {
            $out .= ' static';
        }

        return $out;
    }

    public function setAccessPathFromParent(?InstanceValue $parent): void
    {
        $name = \strtolower($this->getName());

        if ($this->static && !isset(self::MAGIC_NAMES[$name])) {
            $this->access_path = '\\'.$this->owner_class.'::'.$this->name.'()';
        } elseif (null === $parent) {
            $this->access_path = null;
        } else {
            $c = $parent->getContext();
            if ('__construct' === $name) {
                $this->access_path = 'new \\'.$parent->getClassName().'()';
            } elseif (null === $c->getAccessPath()) {
                $this->access_path = null;
            } elseif ('__invoke' === $name) {
                $this->access_path = $c->getAccessPath().'()';
            } elseif ('__clone' === $name) {
                $this->access_path = 'clone '.$c->getAccessPath();
            } elseif ('__tostring' === $name) {
                $this->access_path = '(string) '.$c->getAccessPath();
            } elseif (isset(self::MAGIC_NAMES[$name])) {
                $this->access_path = null;
            } else {
                $this->access_path = $c->getAccessPath().'->'.$this->name.'()';
            }
        }
    }
}
