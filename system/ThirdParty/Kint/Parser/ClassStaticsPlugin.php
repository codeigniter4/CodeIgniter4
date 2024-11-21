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
use Kint\Value\Context\ClassOwnedContext;
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
    /** @psalm-var array<class-string, array<1|0, list<AbstractValue>>> */
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

        $class = $v->getClassName();
        $parser = $this->getParser();
        $r = new ReflectionClass($class);

        $statics_full_name = false;
        $statics = [];
        $props = $r->getProperties(ReflectionProperty::IS_STATIC);
        foreach ($props as $prop) {
            $statics[$prop->name] = $prop;
        }

        $parent = $r;
        while ($parent = $parent->getParentClass()) {
            foreach ($parent->getProperties(ReflectionProperty::IS_STATIC) as $static) {
                if (isset($statics[$static->name]) && $statics[$static->name]->getDeclaringClass()->name === $static->getDeclaringClass()->name) {
                    continue;
                }
                $statics[] = $static;
            }
        }

        $statics_parsed = [];
        $found_statics = [];

        $cdepth = $v->getContext()->getDepth();

        foreach ($statics as $static) {
            $prop = new StaticPropertyContext(
                '$'.$static->getName(),
                $static->getDeclaringClass()->name,
                ClassDeclaredContext::ACCESS_PUBLIC
            );
            $prop->depth = $cdepth + 1;
            $prop->final = KINT_PHP84 && $static->isFinal();

            if ($static->isProtected()) {
                $prop->access = ClassDeclaredContext::ACCESS_PROTECTED;
            } elseif ($static->isPrivate()) {
                $prop->access = ClassDeclaredContext::ACCESS_PRIVATE;
            }

            if ($prop->isAccessible($parser->getCallerClass())) {
                $prop->access_path = '\\'.$prop->owner_class.'::'.$prop->name;
            }

            if (isset($found_statics[$prop->name])) {
                $statics_full_name = true;
            } else {
                $found_statics[$prop->name] = true;

                if ($prop->owner_class !== $class && ClassDeclaredContext::ACCESS_PRIVATE === $prop->access) {
                    $statics_full_name = true;
                }
            }

            if ($statics_full_name) {
                $prop->name = $prop->owner_class.'::'.$prop->name;
            }

            $static->setAccessible(true);

            /**
             * @psalm-suppress TooFewArguments
             * Appears to have been fixed in master
             */
            if (!$static->isInitialized()) {
                $statics_parsed[] = new UninitializedValue($prop);
            } else {
                $static = $static->getValue();
                $statics_parsed[] = $parser->parse($static, $prop);
            }
        }

        if ($statics_parsed) {
            $v->addRepresentation(new ContainerRepresentation('Static properties', $statics_parsed, 'statics'));
        }

        if ($consts = $this->getCachedConstants($r)) {
            $v->addRepresentation(new ContainerRepresentation('Class constants', $consts, 'constants'));
        }

        return $v;
    }

    /** @psalm-return list<AbstractValue> */
    private function getCachedConstants(ReflectionClass $r): array
    {
        $parser = $this->getParser();
        $pdepth = $parser->getDepthLimit();
        $pdepth_enabled = (int) ($pdepth > 0);
        $class = $r->getName();

        // Separate cache for dumping with/without depth limit
        // This means we can do immediate depth limit on normal dumps
        if (!isset($this->cache[$class][$pdepth_enabled])) {
            $consts = [];
            $reflectors = [];

            foreach ($r->getConstants() as $name => $val) {
                $cr = new ReflectionClassConstant($class, $name);

                // Skip enum constants
                if (\is_a($cr->class, UnitEnum::class, true) && $val instanceof UnitEnum && $cr->class === \get_class($val)) {
                    continue;
                }

                $reflectors[$cr->name] = [$cr, $val];
                $consts[$cr->name] = null;
            }

            if ($r = $r->getParentClass()) {
                $parents = $this->getCachedConstants($r);

                foreach ($parents as $value) {
                    $c = $value->getContext();
                    $cname = $c->getName();

                    if (isset($reflectors[$cname]) && $c instanceof ClassOwnedContext && $reflectors[$cname][0]->getDeclaringClass()->name === $c->owner_class) {
                        $consts[$cname] = $value;
                        unset($reflectors[$cname]);
                    } else {
                        $value = clone $value;
                        $c = $value->getContext();
                        if ($c instanceof ClassOwnedContext) {
                            $c->name = $c->owner_class.'::'.$cname;
                        }
                        $consts[] = $value;
                    }
                }
            }

            foreach ($reflectors as [$cr, $val]) {
                $context = new ClassConstContext(
                    $cr->name,
                    $cr->getDeclaringClass()->name,
                    ClassDeclaredContext::ACCESS_PUBLIC
                );
                $context->depth = $pdepth ?: 1;
                $context->final = KINT_PHP81 && $cr->isFinal();

                if ($cr->isProtected()) {
                    $context->access = ClassDeclaredContext::ACCESS_PROTECTED;
                } elseif ($cr->isPrivate()) {
                    $context->access = ClassDeclaredContext::ACCESS_PRIVATE;
                } else {
                    // No access path for protected/private. Tough shit the cache is worth it
                    $context->access_path = '\\'.$context->owner_class.'::'.$context->name;
                }

                $consts[$cr->name] = $parser->parse($val, $context);
            }

            /** @psalm-var AbstractValue[] $consts */
            $this->cache[$class][$pdepth_enabled] = \array_values($consts);
        }

        return $this->cache[$class][$pdepth_enabled];
    }
}
