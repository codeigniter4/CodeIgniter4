<?php

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

use Kint\Object\BasicObject;
use Kint\Object\InstanceObject;
use Kint\Object\MethodObject;
use Kint\Object\Representation\Representation;
use ReflectionClass;

class ClassMethodsPlugin extends Plugin
{
    private static $cache = array();

    public function getTypes()
    {
        return array('object');
    }

    public function getTriggers()
    {
        return Parser::TRIGGER_SUCCESS;
    }

    public function parse(&$var, BasicObject &$o, $trigger)
    {
        $class = \get_class($var);

        // assuming class definition will not change inside one request
        if (!isset(self::$cache[$class])) {
            $methods = array();

            $reflection = new ReflectionClass($class);

            foreach ($reflection->getMethods() as $method) {
                $methods[] = new MethodObject($method);
            }

            \usort($methods, array('Kint\\Parser\\ClassMethodsPlugin', 'sort'));

            self::$cache[$class] = $methods;
        }

        if (!empty(self::$cache[$class])) {
            $rep = new Representation('Available methods', 'methods');

            // Can't cache access paths
            foreach (self::$cache[$class] as $m) {
                $method = clone $m;
                $method->depth = $o->depth + 1;

                if (!$this->parser->childHasPath($o, $method)) {
                    $method->access_path = null;
                } else {
                    $method->setAccessPathFrom($o);
                }

                if ($method->owner_class !== $class && $ds = $method->getRepresentation('docstring')) {
                    $ds = clone $ds;
                    $ds->class = $method->owner_class;
                    $method->replaceRepresentation($ds);
                }

                $rep->contents[] = $method;
            }

            $o->addRepresentation($rep);
        }
    }

    private static function sort(MethodObject $a, MethodObject $b)
    {
        $sort = ((int) $a->static) - ((int) $b->static);
        if ($sort) {
            return $sort;
        }

        $sort = BasicObject::sortByAccess($a, $b);
        if ($sort) {
            return $sort;
        }

        $sort = InstanceObject::sortByHierarchy($a->owner_class, $b->owner_class);
        if ($sort) {
            return $sort;
        }

        return $a->startline - $b->startline;
    }
}
