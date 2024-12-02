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
use Kint\Value\AbstractValue;
use Kint\Value\ClosureValue;
use Kint\Value\Context\BaseContext;
use Kint\Value\Representation\ContainerRepresentation;
use ReflectionFunction;
use ReflectionReference;

class ClosurePlugin extends AbstractPlugin implements PluginCompleteInterface
{
    public function getTypes(): array
    {
        return ['object'];
    }

    public function getTriggers(): int
    {
        return Parser::TRIGGER_SUCCESS;
    }

    public function parseComplete(&$var, AbstractValue $v, int $trigger): AbstractValue
    {
        if (!$var instanceof Closure) {
            return $v;
        }

        $c = $v->getContext();

        $object = new ClosureValue($c, $var);
        $object->flags = $v->flags;
        $object->appendRepresentations($v->getRepresentations());

        $object->removeRepresentation('properties');

        $closure = new ReflectionFunction($var);

        $statics = [];

        if ($v = $closure->getClosureThis()) {
            $statics = ['this' => $v];
        }

        $statics = $statics + $closure->getStaticVariables();

        $cdepth = $c->getDepth();

        if (\count($statics)) {
            $statics_parsed = [];

            $parser = $this->getParser();

            foreach ($statics as $name => $_) {
                $base = new BaseContext('$'.$name);
                $base->depth = $cdepth + 1;
                $base->reference = null !== ReflectionReference::fromArrayElement($statics, $name);
                $statics_parsed[$name] = $parser->parse($statics[$name], $base);
            }

            $object->addRepresentation(new ContainerRepresentation('Uses', $statics_parsed), 0);
        }

        return $object;
    }
}
