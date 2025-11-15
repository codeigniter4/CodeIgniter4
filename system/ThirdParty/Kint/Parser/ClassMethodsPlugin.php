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
use Kint\Value\Context\MethodContext;
use Kint\Value\DeclaredCallableBag;
use Kint\Value\InstanceValue;
use Kint\Value\MethodValue;
use Kint\Value\Representation\ContainerRepresentation;
use ReflectionClass;
use ReflectionMethod;

class ClassMethodsPlugin extends AbstractPlugin implements PluginCompleteInterface
{
    public static bool $show_access_path = true;

    /**
     * Whether to go out of the way to show constructor paths
     * when the instance isn't accessible.
     *
     * Disabling this improves performance.
     */
    public static bool $show_constructor_path = false;

    /** @psalm-var array<class-string, MethodValue[]> */
    private array $instance_cache = [];

    /** @psalm-var array<class-string, MethodValue[]> */
    private array $static_cache = [];

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
        $scope = $this->getParser()->getCallerClass();

        if ($contents = $this->getCachedMethods($class)) {
            if (self::$show_access_path) {
                if (null !== $v->getContext()->getAccessPath()) {
                    // If we have an access path we can generate them for the children
                    foreach ($contents as $key => $val) {
                        if ($val->getContext()->isAccessible($scope)) {
                            $val = clone $val;
                            $val->getContext()->setAccessPathFromParent($v);
                            $contents[$key] = $val;
                        }
                    }
                } elseif (self::$show_constructor_path && isset($contents['__construct'])) {
                    // __construct is the only exception: The only non-static method
                    // that can be called without access to the parent instance.
                    // Technically I guess it really is a static method but so long
                    // as PHP continues to refer to it as a normal one so will we.
                    $val = $contents['__construct'];
                    if ($val->getContext()->isAccessible($scope)) {
                        $val = clone $val;
                        $val->getContext()->setAccessPathFromParent($v);
                        $contents['__construct'] = $val;
                    }
                }
            }

            $v->addRepresentation(new ContainerRepresentation('Methods', $contents));
        }

        if ($contents = $this->getCachedStaticMethods($class)) {
            $v->addRepresentation(new ContainerRepresentation('Static methods', $contents));
        }

        return $v;
    }

    /**
     * @psalm-param class-string $class
     *
     * @psalm-return MethodValue[]
     */
    private function getCachedMethods(string $class): array
    {
        if (!isset($this->instance_cache[$class])) {
            $methods = [];

            $r = new ReflectionClass($class);

            $parent_methods = [];
            if ($parent = \get_parent_class($class)) {
                $parent_methods = $this->getCachedMethods($parent);
            }

            foreach ($r->getMethods() as $mr) {
                if ($mr->isStatic()) {
                    continue;
                }

                $canon_name = \strtolower($mr->name);
                if ($mr->isPrivate() && '__construct' !== $canon_name) {
                    $canon_name = \strtolower($mr->getDeclaringClass()->name).'::'.$canon_name;
                }

                if ($mr->getDeclaringClass()->name === $class) {
                    $method = new MethodValue(new MethodContext($mr), new DeclaredCallableBag($mr));
                    $methods[$canon_name] = $method;
                    unset($parent_methods[$canon_name]);
                } elseif (isset($parent_methods[$canon_name])) {
                    $method = $parent_methods[$canon_name];
                    unset($parent_methods[$canon_name]);

                    if (!$method->getContext()->inherited) {
                        $method = clone $method;
                        $method->getContext()->inherited = true;
                    }

                    $methods[$canon_name] = $method;
                } elseif ($mr->getDeclaringClass()->isInterface()) {
                    $c = new MethodContext($mr);
                    $c->inherited = true;
                    $methods[$canon_name] = new MethodValue($c, new DeclaredCallableBag($mr));
                }
            }

            foreach ($parent_methods as $name => $method) {
                if (!$method->getContext()->inherited) {
                    $method = clone $method;
                    $method->getContext()->inherited = true;
                }

                if ('__construct' === $name) {
                    $methods['__construct'] = $method;
                } else {
                    $methods[] = $method;
                }
            }

            $this->instance_cache[$class] = $methods;
        }

        return $this->instance_cache[$class];
    }

    /**
     * @psalm-param class-string $class
     *
     * @psalm-return MethodValue[]
     */
    private function getCachedStaticMethods(string $class): array
    {
        if (!isset($this->static_cache[$class])) {
            $methods = [];

            $r = new ReflectionClass($class);

            $parent_methods = [];
            if ($parent = \get_parent_class($class)) {
                $parent_methods = $this->getCachedStaticMethods($parent);
            }

            foreach ($r->getMethods(ReflectionMethod::IS_STATIC) as $mr) {
                $canon_name = \strtolower($mr->getDeclaringClass()->name.'::'.$mr->name);

                if ($mr->getDeclaringClass()->name === $class) {
                    $method = new MethodValue(new MethodContext($mr), new DeclaredCallableBag($mr));
                    $methods[$canon_name] = $method;
                } elseif (isset($parent_methods[$canon_name])) {
                    $methods[$canon_name] = $parent_methods[$canon_name];
                } elseif ($mr->getDeclaringClass()->isInterface()) {
                    $c = new MethodContext($mr);
                    $c->inherited = true;
                    $methods[$canon_name] = new MethodValue($c, new DeclaredCallableBag($mr));
                }

                unset($parent_methods[$canon_name]);
            }

            $this->static_cache[$class] = $methods + $parent_methods;
        }

        return $this->static_cache[$class];
    }
}
