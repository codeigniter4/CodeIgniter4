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
use Kint\Value\Context\ContextInterface;
use Kint\Value\Representation\ContainerRepresentation;

class StreamValue extends ResourceValue
{
    /**
     * @psalm-readonly
     *
     * @psalm-var AbstractValue[]
     */
    protected array $stream_meta;
    /** @psalm-readonly */
    protected ?string $uri;

    /** @psalm-param AbstractValue[] $stream_meta */
    public function __construct(ContextInterface $context, array $stream_meta, ?string $uri)
    {
        parent::__construct($context, 'stream');
        $this->stream_meta = $stream_meta;
        $this->uri = $uri;

        if ($stream_meta) {
            $this->addRepresentation(new ContainerRepresentation('Stream', $stream_meta, null, true));
        }
    }

    public function getHint(): string
    {
        return parent::getHint() ?? 'stream';
    }

    public function getDisplayValue(): ?string
    {
        if (null === $this->uri) {
            return null;
        }

        if ('/' === $this->uri[0] && \stream_is_local($this->uri)) {
            return Utils::shortenPath($this->uri);
        }

        return $this->uri;
    }

    public function getDisplayChildren(): array
    {
        return $this->stream_meta;
    }
}
