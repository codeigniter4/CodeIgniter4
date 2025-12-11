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

use RuntimeException;

class SourceRepresentation extends AbstractRepresentation
{
    private const DEFAULT_PADDING = 7;

    /**
     * @psalm-readonly
     *
     * @psalm-var non-empty-array<string>
     */
    protected array $source;
    /** @psalm-readonly */
    protected string $filename;
    /** @psalm-readonly */
    protected int $line;
    /** @psalm-readonly */
    protected bool $showfilename;

    public function __construct(string $filename, int $line, ?int $padding = self::DEFAULT_PADDING, bool $showfilename = false)
    {
        parent::__construct('Source');

        $this->filename = $filename;
        $this->line = $line;
        $this->showfilename = $showfilename;

        $padding ??= self::DEFAULT_PADDING;

        $start_line = \max($line - $padding, 1);
        $length = $line + $padding + 1 - $start_line;
        $this->source = self::readSource($filename, $start_line, $length);
    }

    public function getHint(): string
    {
        return 'source';
    }

    /**
     * @psalm-api
     *
     * @psalm-return non-empty-string
     */
    public function getSourceSlice(): string
    {
        return \implode("\n", $this->source);
    }

    /** @psalm-return non-empty-array<string> */
    public function getSourceLines(): array
    {
        return $this->source;
    }

    public function getFileName(): string
    {
        return $this->filename;
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function showFileName(): bool
    {
        return $this->showfilename;
    }

    /**
     * Gets section of source code.
     *
     * @psalm-return non-empty-array<string>
     */
    private static function readSource(string $filename, int $start_line = 1, ?int $length = null): array
    {
        if (!$filename || !\file_exists($filename) || !\is_readable($filename)) {
            throw new RuntimeException("Couldn't read file");
        }

        /** @psalm-var non-empty-array<string> $source */
        $source = \preg_split("/\r\n|\n|\r/", (string) \file_get_contents($filename));
        /** @psalm-var array $source */
        $source = \array_combine(\range(1, \count($source)), $source);
        $source = \array_slice($source, $start_line - 1, $length, true);

        if (0 === \count($source)) {
            throw new RuntimeException('File seemed to be empty');
        }

        return $source;
    }
}
