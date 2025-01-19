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

namespace Kint\Value\Representation;

use InvalidArgumentException;

class CallableDefinitionRepresentation extends AbstractRepresentation
{
    /** @psalm-readonly */
    protected string $filename;
    /** @psalm-readonly */
    protected int $line;
    /**
     * @psalm-readonly
     *
     * @psalm-var ?non-empty-string
     */
    protected ?string $docstring;

    /**
     * @psalm-param ?non-empty-string $docstring
     */
    public function __construct(string $filename, int $line, ?string $docstring)
    {
        if (null !== $docstring && !\preg_match('%^/\\*\\*.+\\*/$%s', $docstring)) {
            throw new InvalidArgumentException('Docstring is invalid');
        }

        parent::__construct('Callable definition', null, true);

        $this->filename = $filename;
        $this->line = $line;
        $this->docstring = $docstring;
    }

    public function getHint(): string
    {
        return 'callable';
    }

    public function getFileName(): string
    {
        return $this->filename;
    }

    public function getLine(): int
    {
        return $this->line;
    }

    /**
     * @psalm-api
     *
     * @psalm-return ?non-empty-string
     */
    public function getDocstring(): ?string
    {
        return $this->docstring;
    }

    /**
     * Returns the representation's docstring without surrounding comments.
     *
     * Note that this will not work flawlessly.
     *
     * On comments with whitespace after the stars the lines will begin with
     * whitespace, since we can't accurately guess how much of an indentation
     * is required.
     *
     * And on lines without stars on the left this may eat bullet points.
     *
     * Long story short: If you want the docstring read the contents. If you
     * absolutely must have it without comments (ie renderValueShort) this will
     * probably do.
     */
    public function getDocstringWithoutComments(): ?string
    {
        if (null === ($ds = $this->getDocstring())) {
            return null;
        }

        $string = \substr($ds, 3, -2);
        $string = \preg_replace('/^\\s*\\*\\s*?(\\S|$)/m', '\\1', $string);

        return \trim($string);
    }

    public function getDocstringFirstLine(): ?string
    {
        $ds = $this->getDocstringWithoutComments();

        if (null === $ds) {
            return null;
        }

        $ds = \explode("\n", $ds);

        $out = '';

        foreach ($ds as $line) {
            if (0 === \strlen(\trim($line)) || '@' === $line[0]) {
                break;
            }

            $out .= $line.' ';
        }

        if (\strlen($out)) {
            return \rtrim($out);
        }

        return null;
    }

    public function getDocstringTrimmed(): ?string
    {
        if (null === ($ds = $this->getDocstring())) {
            return null;
        }

        $docstring = [];
        foreach (\explode("\n", $ds) as $line) {
            $line = \trim($line);
            if (($line[0] ?? null) === '*') {
                $line = ' '.$line;
            }
            $docstring[] = $line;
        }

        return \implode("\n", $docstring);
    }
}
