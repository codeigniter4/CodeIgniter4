<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\HotReloader;

use Config\Toolbar;
use RecursiveFilterIterator;
use RecursiveIterator;

/**
 * @internal
 *
 * @psalm-suppress MissingTemplateParam
 */
final class IteratorFilter extends RecursiveFilterIterator implements RecursiveIterator
{
    private array $watchedExtensions = [];

    public function __construct(RecursiveIterator $iterator)
    {
        parent::__construct($iterator);

        $this->watchedExtensions = config(Toolbar::class)->watchedExtensions;
    }

    /**
     * Apply filters to the files in the iterator.
     */
    public function accept(): bool
    {
        if (! $this->current()->isFile()) {
            return true;
        }

        $filename = $this->current()->getFilename();

        // Skip hidden files and directories.
        if ($filename[0] === '.') {
            return false;
        }

        // Only consume files of interest.
        $ext = trim(strtolower($this->current()->getExtension()), '. ');

        return in_array($ext, $this->watchedExtensions, true);
    }
}
