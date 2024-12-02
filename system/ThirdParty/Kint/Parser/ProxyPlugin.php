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

/**
 * @psalm-import-type ParserTrigger from Parser
 *
 * @psalm-api
 */
class ProxyPlugin implements PluginBeginInterface, PluginCompleteInterface
{
    protected array $types;
    /** @psalm-var ParserTrigger */
    protected int $triggers;
    /** @psalm-var callable */
    protected $callback;
    private ?Parser $parser = null;

    /**
     * @psalm-param ParserTrigger $triggers
     * @psalm-param callable $callback
     */
    public function __construct(array $types, int $triggers, $callback)
    {
        $this->types = $types;
        $this->triggers = $triggers;
        $this->callback = $callback;
    }

    public function setParser(Parser $p): void
    {
        $this->parser = $p;
    }

    public function getTypes(): array
    {
        return $this->types;
    }

    public function getTriggers(): int
    {
        return $this->triggers;
    }

    public function parseBegin(&$var, ContextInterface $c): ?AbstractValue
    {
        return \call_user_func_array($this->callback, [
            &$var,
            $c,
            Parser::TRIGGER_BEGIN,
            $this->parser,
        ]);
    }

    public function parseComplete(&$var, AbstractValue $v, int $trigger): AbstractValue
    {
        return \call_user_func_array($this->callback, [
            &$var,
            $v,
            $trigger,
            $this->parser,
        ]);
    }
}
