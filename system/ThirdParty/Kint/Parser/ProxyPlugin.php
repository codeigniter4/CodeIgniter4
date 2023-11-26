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

use InvalidArgumentException;
use Kint\Zval\Value;

class ProxyPlugin implements PluginInterface
{
    protected $parser;
    protected $types;
    protected $triggers;
    protected $callback;

    /**
     * @param callable $callback
     */
    public function __construct(array $types, int $triggers, $callback)
    {
        if (!\is_callable($callback)) {
            throw new InvalidArgumentException('ProxyPlugin callback must be callable');
        }

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

    public function parse(&$var, Value &$o, int $trigger): void
    {
        \call_user_func_array($this->callback, [&$var, &$o, $trigger, $this->parser]);
    }
}
