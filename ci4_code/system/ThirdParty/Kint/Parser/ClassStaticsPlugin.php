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
use Kint\Object\Representation\Representation;
use ReflectionClass;
use ReflectionProperty;

class ClassStaticsPlugin extends Plugin
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
        $reflection = new ReflectionClass($class);

        // Constants
        // TODO: PHP 7.1 allows private consts but reflection doesn't have a way to check them yet
        if (!isset(self::$cache[$class])) {
            $consts = array();

            foreach ($reflection->getConstants() as $name => $val) {
                $const = BasicObject::blank($name, '\\'.$class.'::'.$name);
                $const->const = true;
                $const->depth = $o->depth + 1;
                $const->owner_class = $class;
                $const->operator = BasicObject::OPERATOR_STATIC;
                $const = $this->parser->parse($val, $const);

                $consts[] = $const;
            }

            self::$cache[$class] = $consts;
        }

        $statics = new Representation('Static class properties', 'statics');
        $statics->contents = self::$cache[$class];

        foreach ($reflection->getProperties(ReflectionProperty::IS_STATIC) as $static) {
            $prop = new BasicObject();
            $prop->name = '$'.$static->getName();
            $prop->depth = $o->depth + 1;
            $prop->static = true;
            $prop->operator = BasicObject::OPERATOR_STATIC;
            $prop->owner_class = $static->getDeclaringClass()->name;

            $prop->access = BasicObject::ACCESS_PUBLIC;
            if ($static->isProtected()) {
                $prop->access = BasicObject::ACCESS_PROTECTED;
            } elseif ($static->isPrivate()) {
                $prop->access = BasicObject::ACCESS_PRIVATE;
            }

            if ($this->parser->childHasPath($o, $prop)) {
                $prop->access_path = '\\'.$prop->owner_class.'::'.$prop->name;
            }

            $static->setAccessible(true);
            $static = $static->getValue();
            $statics->contents[] = $this->parser->parse($static, $prop);
        }

        if (empty($statics->contents)) {
            return;
        }

        \usort($statics->contents, array('Kint\\Parser\\ClassStaticsPlugin', 'sort'));

        $o->addRepresentation($statics);
    }

    private static function sort(BasicObject $a, BasicObject $b)
    {
        $sort = ((int) $a->const) - ((int) $b->const);
        if ($sort) {
            return $sort;
        }

        $sort = BasicObject::sortByAccess($a, $b);
        if ($sort) {
            return $sort;
        }

        return InstanceObject::sortByHierarchy($a->owner_class, $b->owner_class);
    }
}
