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

namespace Kint\Parser;

use Kint\Value\AbstractValue;
use Kint\Value\Context\ClassConstContext;
use Kint\Value\Context\ClassDeclaredContext;
use Kint\Value\Context\StaticPropertyContext;
use Kint\Value\InstanceValue;
use Kint\Value\Representation\ContainerRepresentation;
use Kint\Value\UninitializedValue;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionProperty;
use UnitEnum;

class ClassStaticsPlugin extends AbstractPlugin implements PluginCompleteInterface
{
    /** @psalm-var array<class-string, array<1|0, array<AbstractValue>>> */
    private array $cache = [];

    public function getTypes(): array
    {
        return ['object'];
    }

    public function getTriggers(): int
    {
        return Parser::TRIGGER_SUCCESS;
    }

    /**
     * @psalm-template T of AbstractValue
     *
     * @psalm-param mixed $var
     * @psalm-param T $v
     *
     * @psalm-return T
     */
    public function parseComplete(&$var, AbstractValue $v, int $trigger): AbstractValue
    {
        if (!$v instanceof InstanceValue) {
            return $v;
        }

        $deep = 0 === $this->getParser()->getDepthLimit();

        $r = new ReflectionClass($v->getClassName());

        if ($statics = $this->getStatics($r, $v->getContext()->getDepth() + 1)) {
            $v->addRepresentation(new ContainerRepresentation('Static properties', \array_values($statics), 'statics'));
        }

        if ($consts = $this->getCachedConstants($r, $deep)) {
            $v->addRepresentation(new ContainerRepresentation('Class constants', \array_values($consts), 'constants'));
        }

        return $v;
    }

    /** @psalm-return array<AbstractValue> */
    private function getStatics(ReflectionClass $r, int $depth): array
    {
        $cdepth = $depth ?: 1;
        $class = $r->getName();
        $parent = $r->getParentClass();

        $parent_statics = $parent ? $this->getStatics($parent, $depth) : [];
        $statics = [];

        foreach ($r->getProperties(ReflectionProperty::IS_STATIC) as $pr) {
            $canon_name = \strtolower($pr->getDeclaringClass()->name.'::'.$pr->name);

            if ($pr->getDeclaringClass()->name === $class) {
                $statics[$canon_name] = $this->buildStaticValue($pr, $cdepth);
            } elseif (isset($parent_statics[$canon_name])) {
                $statics[$canon_name] = $parent_statics[$canon_name];
                unset($parent_statics[$canon_name]);
            } else {
                // This should never happen since abstract static properties can't exist
                $statics[$canon_name] = $this->buildStaticValue($pr, $cdepth); // @codeCoverageIgnore
            }
        }

        foreach ($parent_statics as $canon_name => $value) {
            $statics[$canon_name] = $value;
        }

        return $statics;
    }

    private function buildStaticValue(ReflectionProperty $pr, int $depth): AbstractValue
    {
        $context = new StaticPropertyContext(
            $pr->name,
            $pr->getDeclaringClass()->name,
            ClassDeclaredContext::ACCESS_PUBLIC
        );
        $context->depth = $depth;
        $context->final = KINT_PHP84 && $pr->isFinal();

        if ($pr->isProtected()) {
            $context->access = ClassDeclaredContext::ACCESS_PROTECTED;
        } elseif ($pr->isPrivate()) {
            $context->access = ClassDeclaredContext::ACCESS_PRIVATE;
        }

        $parser = $this->getParser();

        if ($context->isAccessible($parser->getCallerClass())) {
            $context->access_path = '\\'.$context->owner_class.'::$'.$context->name;
        }

        $pr->setAccessible(true);

        /**
         * @psalm-suppress TooFewArguments
         * Appears to have been fixed in master.
         */
        if (!$pr->isInitialized()) {
            $context->access_path = null;

            return new UninitializedValue($context);
        }

        $val = $pr->getValue();

        $out = $this->getParser()->parse($val, $context);
        $context->access_path = null;

        return $out;
    }

    /** @psalm-return array<AbstractValue> */
    private function getCachedConstants(ReflectionClass $r, bool $deep): array
    {
        $parser = $this->getParser();
        $cdepth = $parser->getDepthLimit() ?: 1;
        $deepkey = (int) $deep;
        $class = $r->getName();

        // Separate cache for dumping with/without depth limit
        // This means we can do immediate depth limit on normal dumps
        if (!isset($this->cache[$class][$deepkey])) {
            $consts = [];

            $parent_consts = [];
            if ($parent = $r->getParentClass()) {
                $parent_consts = $this->getCachedConstants($parent, $deep);
            }
            foreach ($r->getConstants() as $name => $val) {
                $cr = new ReflectionClassConstant($class, $name);

                // Skip enum constants
                if ($cr->class === $class && \is_a($class, UnitEnum::class, true)) {
                    continue;
                }

                $canon_name = \strtolower($cr->getDeclaringClass()->name.'::'.$name);

                if ($cr->getDeclaringClass()->name === $class) {
                    $context = $this->buildConstContext($cr);
                    $context->depth = $cdepth;

                    $consts[$canon_name] = $parser->parse($val, $context);
                    $context->access_path = null;
                } elseif (isset($parent_consts[$canon_name])) {
                    $consts[$canon_name] = $parent_consts[$canon_name];
                } else {
                    $context = $this->buildConstContext($cr);
                    $context->depth = $cdepth;

                    $consts[$canon_name] = $parser->parse($val, $context);
                    $context->access_path = null;
                }

                unset($parent_consts[$canon_name]);
            }

            $this->cache[$class][$deepkey] = $consts + $parent_consts;
        }

        return $this->cache[$class][$deepkey];
    }

    private function buildConstContext(ReflectionClassConstant $cr): ClassConstContext
    {
        $context = new ClassConstContext(
            $cr->name,
            $cr->getDeclaringClass()->name,
            ClassDeclaredContext::ACCESS_PUBLIC
        );
        $context->final = KINT_PHP81 && $cr->isFinal();

        if ($cr->isProtected()) {
            $context->access = ClassDeclaredContext::ACCESS_PROTECTED;
        } elseif ($cr->isPrivate()) {
            $context->access = ClassDeclaredContext::ACCESS_PRIVATE;
        } else {
            $context->access_path = '\\'.$context->owner_class.'::'.$context->name;
        }

        return $context;
    }
}
