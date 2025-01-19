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
use Kint\Value\Context\ContextInterface;
use Kint\Value\InstanceValue;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class BlacklistPlugin extends AbstractPlugin implements PluginBeginInterface
{
    /**
     * List of classes and interfaces to blacklist.
     *
     * @var class-string[]
     */
    public static array $blacklist = [];

    /**
     * List of classes and interfaces to blacklist except when dumped directly.
     *
     * @var class-string[]
     */
    public static array $shallow_blacklist = [
        ContainerInterface::class,
        EventDispatcherInterface::class,
    ];

    public function getTypes(): array
    {
        return ['object'];
    }

    public function getTriggers(): int
    {
        return Parser::TRIGGER_BEGIN;
    }

    public function parseBegin(&$var, ContextInterface $c): ?AbstractValue
    {
        foreach (self::$blacklist as $class) {
            if ($var instanceof $class) {
                return $this->blacklistValue($var, $c);
            }
        }

        if ($c->getDepth() <= 0) {
            return null;
        }

        foreach (self::$shallow_blacklist as $class) {
            if ($var instanceof $class) {
                return $this->blacklistValue($var, $c);
            }
        }

        return null;
    }

    /**
     * @param object &$var
     */
    protected function blacklistValue(&$var, ContextInterface $c): InstanceValue
    {
        $object = new InstanceValue($c, \get_class($var), \spl_object_hash($var), \spl_object_id($var));
        $object->flags |= AbstractValue::FLAG_BLACKLIST;

        return $object;
    }
}
