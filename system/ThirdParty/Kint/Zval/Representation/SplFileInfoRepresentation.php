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

namespace Kint\Zval\Representation;

use Kint\Utils;
use RuntimeException;
use SplFileInfo;

class SplFileInfoRepresentation extends Representation
{
    public $perms = null;
    public $flags;
    public $path;
    public $realpath = null;
    public $linktarget = null;
    public $size = null;
    public $is_dir = false;
    public $is_file = false;
    public $is_link = false;
    public $owner = null;
    public $group = null;
    public $ctime = null;
    public $mtime = null;
    public $typename = 'Unknown file';
    public $typeflag = '-';
    public $hints = ['fspath'];

    public function __construct(SplFileInfo $fileInfo)
    {
        parent::__construct('SplFileInfo');

        $this->path = $fileInfo->getPathname();

        try {
            if (\strlen($this->path) && $fileInfo->getRealPath()) {
                $this->perms = $fileInfo->getPerms();
                $this->size = $fileInfo->getSize();
                $this->owner = $fileInfo->getOwner();
                $this->group = $fileInfo->getGroup();
                $this->ctime = $fileInfo->getCTime();
                $this->mtime = $fileInfo->getMTime();
                $this->realpath = $fileInfo->getRealPath();
            }

            $this->is_dir = $fileInfo->isDir();
            $this->is_file = $fileInfo->isFile();
            $this->is_link = $fileInfo->isLink();

            if ($this->is_link) {
                $this->linktarget = $fileInfo->getLinkTarget();
            }
        } catch (RuntimeException $e) {
            if (false === \strpos($e->getMessage(), ' open_basedir ')) {
                throw $e;
            }
        }

        switch ($this->perms & 0xF000) {
            case 0xC000:
                $this->typename = 'Socket';
                $this->typeflag = 's';
                break;
            case 0x6000:
                $this->typename = 'Block device';
                $this->typeflag = 'b';
                break;
            case 0x2000:
                $this->typename = 'Character device';
                $this->typeflag = 'c';
                break;
            case 0x1000:
                $this->typename = 'Named pipe';
                $this->typeflag = 'p';
                break;
            default:
                if ($this->is_file) {
                    if ($this->is_link) {
                        $this->typename = 'File symlink';
                        $this->typeflag = 'l';
                    } else {
                        $this->typename = 'File';
                        $this->typeflag = '-';
                    }
                } elseif ($this->is_dir) {
                    if ($this->is_link) {
                        $this->typename = 'Directory symlink';
                        $this->typeflag = 'l';
                    } else {
                        $this->typename = 'Directory';
                        $this->typeflag = 'd';
                    }
                }
                break;
        }

        $this->flags = [$this->typeflag];

        // User
        $this->flags[] = (($this->perms & 0400) ? 'r' : '-');
        $this->flags[] = (($this->perms & 0200) ? 'w' : '-');
        if ($this->perms & 0100) {
            $this->flags[] = ($this->perms & 04000) ? 's' : 'x';
        } else {
            $this->flags[] = ($this->perms & 04000) ? 'S' : '-';
        }

        // Group
        $this->flags[] = (($this->perms & 0040) ? 'r' : '-');
        $this->flags[] = (($this->perms & 0020) ? 'w' : '-');
        if ($this->perms & 0010) {
            $this->flags[] = ($this->perms & 02000) ? 's' : 'x';
        } else {
            $this->flags[] = ($this->perms & 02000) ? 'S' : '-';
        }

        // Other
        $this->flags[] = (($this->perms & 0004) ? 'r' : '-');
        $this->flags[] = (($this->perms & 0002) ? 'w' : '-');
        if ($this->perms & 0001) {
            $this->flags[] = ($this->perms & 01000) ? 's' : 'x';
        } else {
            $this->flags[] = ($this->perms & 01000) ? 'S' : '-';
        }

        $this->contents = \implode($this->flags).' '.$this->owner.' '.$this->group;
        $this->contents .= ' '.$this->getSize().' '.$this->getMTime().' ';

        if ($this->is_link && $this->linktarget) {
            $this->contents .= $this->path.' -> '.$this->linktarget;
        } elseif (null !== $this->realpath && \strlen($this->realpath) < \strlen($this->path)) {
            $this->contents .= $this->realpath;
        } else {
            $this->contents .= $this->path;
        }
    }

    public function getLabel(): string
    {
        if ($size = $this->getSize()) {
            return $this->typename.' ('.$size.')';
        }

        return $this->typename;
    }

    public function getSize(): ?string
    {
        if ($this->size) {
            $size = Utils::getHumanReadableBytes($this->size);

            return \round($size['value'], 2).$size['unit'];
        }

        return null;
    }

    public function getMTime(): ?string
    {
        if (null !== $this->mtime) {
            $year = \date('Y', $this->mtime);

            if ($year !== \date('Y')) {
                return \date('M d Y', $this->mtime);
            }

            return \date('M d H:i', $this->mtime);
        }

        return null;
    }
}
