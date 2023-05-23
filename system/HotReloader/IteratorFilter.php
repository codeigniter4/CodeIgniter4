<?php

namespace CodeIgniter\HotReloader;

use RecursiveFilterIterator;
use RecursiveIterator;

/**
 * @internal
 */
class IteratorFilter extends RecursiveFilterIterator implements RecursiveIterator
{
    private array $watchedExtensions = [];

    public function __construct(RecursiveIterator $iterator)
    {
        parent::__construct($iterator);

        $this->watchedExtensions = config('Toolbar')->watchedExtensions;
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
        return in_array($ext, $this->watchedExtensions);
    }
}
