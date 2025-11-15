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
use RuntimeException;
use SplFileInfo;

class SplFileInfoValue extends InstanceValue
{
    /** @psalm-readonly */
    protected string $path;
    /** @psalm-readonly */
    protected ?int $filesize = null;

    public function __construct(ContextInterface $context, SplFileInfo $info)
    {
        parent::__construct($context, \get_class($info), \spl_object_hash($info), \spl_object_id($info));

        $this->path = $info->getPathname();

        try {
            // SplFileInfo::getRealPath will return cwd when path is ''
            if ('' !== $this->path && $info->getRealPath()) {
                $this->filesize = $info->getSize();
            }
        } catch (RuntimeException $e) {
            if (false === \strpos($e->getMessage(), ' open_basedir ')) {
                throw $e; // @codeCoverageIgnore
            }
        }
    }

    public function getHint(): string
    {
        return parent::getHint() ?? 'splfileinfo';
    }

    /** @psalm-api */
    public function getFileSize(): ?int
    {
        return $this->filesize;
    }

    public function getDisplaySize(): ?string
    {
        if (null === $this->filesize) {
            return null;
        }

        $size = Utils::getHumanReadableBytes($this->filesize);

        return $size['value'].$size['unit'];
    }

    public function getDisplayValue(): ?string
    {
        $shortpath = Utils::shortenPath($this->path);

        if ($shortpath !== $this->path) {
            return $shortpath;
        }

        return parent::getDisplayValue();
    }
}
