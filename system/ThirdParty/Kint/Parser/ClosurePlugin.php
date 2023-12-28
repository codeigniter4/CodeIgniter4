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

use Closure;
use Kint\Zval\ClosureValue;
use Kint\Zval\ParameterValue;
use Kint\Zval\Representation\Representation;
use Kint\Zval\Value;
use ReflectionFunction;

class ClosurePlugin extends AbstractPlugin
{
    public function getTypes(): array
    {
        return ['object'];
    }

    public function getTriggers(): int
    {
        return Parser::TRIGGER_SUCCESS;
    }

    public function parse(&$var, Value &$o, int $trigger): void
    {
        if (!$var instanceof Closure) {
            return;
        }

        $object = new ClosureValue();
        $object->transplant($o);
        $o = $object;
        $object->removeRepresentation('properties');

        $closure = new ReflectionFunction($var);

        $o->filename = $closure->getFileName();
        $o->startline = $closure->getStartLine();

        foreach ($closure->getParameters() as $param) {
            $o->parameters[] = new ParameterValue($param);
        }

        $p = new Representation('Parameters');
        $p->contents = $o->parameters;
        $o->addRepresentation($p, 0);

        $statics = [];

        if ($v = $closure->getClosureThis()) {
            $statics = ['this' => $v];
        }

        if (\count($statics = $statics + $closure->getStaticVariables())) {
            $statics_parsed = [];

            foreach ($statics as $name => &$static) {
                $obj = Value::blank('$'.$name);
                $obj->depth = $o->depth + 1;
                $statics_parsed[$name] = $this->parser->parse($static, $obj);
                if (null === $statics_parsed[$name]->value) {
                    $statics_parsed[$name]->access_path = null;
                }
            }

            $r = new Representation('Uses');
            $r->contents = $statics_parsed;
            $o->addRepresentation($r, 0);
        }
    }
}
