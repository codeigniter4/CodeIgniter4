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

namespace Kint\Value;

use InvalidArgumentException;
use Kint\Value\Context\BaseContext;
use Kint\Value\Context\MethodContext;
use ReflectionFunction;
use ReflectionMethod;

/**
 * @psalm-type TraceFrame array{
 *   function: string,
 *   line?: int,
 *   file?: string,
 *   class?: class-string,
 *   object?: object,
 *   type?: string,
 *   args?: list<mixed>
 * }
 */
class TraceFrameValue extends ArrayValue
{
    /** @psalm-readonly */
    protected ?string $file;

    /** @psalm-readonly */
    protected ?int $line;

    /**
     * @psalm-readonly
     *
     * @psalm-var null|FunctionValue|MethodValue
     */
    protected $callable;

    /**
     * @psalm-readonly
     *
     * @psalm-var list<AbstractValue>
     */
    protected array $args;

    /** @psalm-readonly */
    protected ?InstanceValue $object;

    /**
     * @psalm-param TraceFrame $raw_frame
     */
    public function __construct(ArrayValue $old, $raw_frame)
    {
        parent::__construct($old->getContext(), $old->getSize(), $old->getContents());

        $this->file = $raw_frame['file'] ?? null;
        $this->line = $raw_frame['line'] ?? null;

        if (isset($raw_frame['class']) && \method_exists($raw_frame['class'], $raw_frame['function'])) {
            $func = new ReflectionMethod($raw_frame['class'], $raw_frame['function']);
            $this->callable = new MethodValue(
                new MethodContext($func),
                new DeclaredCallableBag($func)
            );
        } elseif (!isset($raw_frame['class']) && \function_exists($raw_frame['function'])) {
            $func = new ReflectionFunction($raw_frame['function']);
            $this->callable = new FunctionValue(
                new BaseContext($raw_frame['function']),
                new DeclaredCallableBag($func)
            );
        } else {
            // Mostly closures, no way to get them
            $this->callable = null;
        }

        foreach ($this->contents as $frame_prop) {
            $c = $frame_prop->getContext();

            if ('object' === $c->getName()) {
                if (!$frame_prop instanceof InstanceValue) {
                    throw new InvalidArgumentException('object key of TraceFrameValue must be parsed to InstanceValue');
                }

                $this->object = $frame_prop;
            }

            if ('args' === $c->getName()) {
                if (!$frame_prop instanceof ArrayValue) {
                    throw new InvalidArgumentException('args key of TraceFrameValue must be parsed to ArrayValue');
                }

                $args = \array_values($frame_prop->getContents());

                if ($this->callable) {
                    foreach ($this->callable->getCallableBag()->parameters as $param) {
                        if (!isset($args[$param->position])) {
                            break; // Optional args follow
                        }

                        $arg = $args[$param->position];

                        if ($arg->getContext() instanceof BaseContext) {
                            $arg = clone $arg;
                            $c = $arg->getContext();

                            if (!$c instanceof BaseContext) {
                                throw new InvalidArgumentException('TraceFrameValue expects arg contexts to be instanceof BaseContext');
                            }

                            $c->name = '$'.$param->name;

                            $args[$param->position] = $arg;
                        }
                    }
                }

                $this->args = $args;
            }
        }

        /**
         * @psalm-suppress DocblockTypeContradiction
         * @psalm-suppress RedundantPropertyInitializationCheck
         * Psalm bug #11124
         */
        $this->args ??= [];
        $this->object ??= null;
    }

    public function getHint(): string
    {
        return parent::getHint() ?? 'trace_frame';
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function getLine(): ?int
    {
        return $this->line;
    }

    /** @psalm-return null|FunctionValue|MethodValue */
    public function getCallable()
    {
        return $this->callable;
    }

    /** @psalm-return list<AbstractValue> */
    public function getArgs(): array
    {
        return $this->args;
    }

    public function getObject(): ?InstanceValue
    {
        return $this->object;
    }
}
