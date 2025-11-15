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

use Kint\Utils;
use ReflectionFunctionAbstract;

/** @psalm-api */
final class DeclaredCallableBag
{
    use ParameterHoldingTrait;

    /** @psalm-readonly */
    public bool $internal;
    /** @psalm-readonly */
    public ?string $filename;
    /** @psalm-readonly */
    public ?int $startline;
    /** @psalm-readonly */
    public ?int $endline;
    /**
     * @psalm-readonly
     *
     * @psalm-var ?non-empty-string
     */
    public ?string $docstring;
    /** @psalm-readonly */
    public bool $return_reference;
    /** @psalm-readonly */
    public ?string $returntype = null;

    public function __construct(ReflectionFunctionAbstract $callable)
    {
        $this->internal = $callable->isInternal();
        $t = $callable->getFileName();
        $this->filename = false === $t ? null : $t;
        $t = $callable->getStartLine();
        $this->startline = false === $t ? null : $t;
        $t = $callable->getEndLine();
        $this->endline = false === $t ? null : $t;
        $t = $callable->getDocComment();
        $this->docstring = false === $t ? null : $t;
        $this->return_reference = $callable->returnsReference();

        $rt = $callable->getReturnType();
        if ($rt) {
            $this->returntype = Utils::getTypeString($rt);
        }

        $parameters = [];
        foreach ($callable->getParameters() as $param) {
            $parameters[] = new ParameterBag($param);
        }
        $this->parameters = $parameters;
    }
}
