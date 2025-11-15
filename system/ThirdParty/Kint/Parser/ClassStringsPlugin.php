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
use Kint\Value\Context\BaseContext;
use Kint\Value\InstanceValue;
use ReflectionClass;

class ClassStringsPlugin extends AbstractPlugin implements PluginCompleteInterface
{
    public static array $blacklist = [];

    protected ClassMethodsPlugin $methods_plugin;
    protected ClassStaticsPlugin $statics_plugin;

    public function __construct(Parser $parser)
    {
        parent::__construct($parser);

        $this->methods_plugin = new ClassMethodsPlugin($parser);
        $this->statics_plugin = new ClassStaticsPlugin($parser);
    }

    public function setParser(Parser $p): void
    {
        parent::setParser($p);

        $this->methods_plugin->setParser($p);
        $this->statics_plugin->setParser($p);
    }

    public function getTypes(): array
    {
        return ['string'];
    }

    public function getTriggers(): int
    {
        return Parser::TRIGGER_SUCCESS;
    }

    public function parseComplete(&$var, AbstractValue $v, int $trigger): AbstractValue
    {
        $c = $v->getContext();

        if ($c->getDepth() > 0) {
            return $v;
        }

        if (!\class_exists($var, true)) {
            return $v;
        }

        if (\in_array($var, self::$blacklist, true)) {
            return $v;
        }

        $r = new ReflectionClass($var);

        $fakeC = new BaseContext($c->getName());
        $fakeC->access_path = null;
        $fakeV = new InstanceValue($fakeC, $r->getName(), 'badhash', -1);
        $fakeVar = null;

        $fakeV = $this->methods_plugin->parseComplete($fakeVar, $fakeV, Parser::TRIGGER_SUCCESS);
        $fakeV = $this->statics_plugin->parseComplete($fakeVar, $fakeV, Parser::TRIGGER_SUCCESS);

        foreach (['methods', 'static_methods', 'statics', 'constants'] as $rep) {
            if ($rep = $fakeV->getRepresentation($rep)) {
                $v->addRepresentation($rep);
            }
        }

        return $v;
    }
}
