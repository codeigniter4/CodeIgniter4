<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Files;

use CodeIgniter\Files\Exceptions\FileException;
use CodeIgniter\Files\Exceptions\FileNotFoundException;
use Countable;
use Generator;
use InvalidArgumentException;
use IteratorAggregate;

/**
 * File Collection Class
 *
 * Representation for a group of files, with utilities for locating,
 * filtering, and ordering them.
 *
 * @template-implements IteratorAggregate<int, File>
 * @see \CodeIgniter\Files\FileCollectionTest
 */
class FileCollection implements Countable, IteratorAggregate
{
    /**
     * The current list of file paths.
     *
     * @var list<string>
     */
    protected $files = [];

    // --------------------------------------------------------------------
    // Support Methods
    // --------------------------------------------------------------------

    /**
     * Resolves a full path and verifies it is an actual directory.
     *
     * @throws FileException
     */
    final protected static function resolveDirectory(string $directory): string
    {
        if (! is_dir($directory = set_realpath($directory))) {
            $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];

            throw FileException::forExpectedDirectory($caller['function']);
        }

        return $directory;
    }

    /**
     * Resolves a full path and verifies it is an actual file.
     *
     * @throws FileException
     */
    final protected static function resolveFile(string $file): string
    {
        if (! is_file($file = set_realpath($file))) {
            $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];

            throw FileException::forExpectedFile($caller['function']);
        }

        return $file;
    }

    /**
     * Removes files that are not part of the given directory (recursive).
     *
     * @param list<string> $files
     *
     * @return list<string>
     */
    final protected static function filterFiles(array $files, string $directory): array
    {
        $directory = self::resolveDirectory($directory);

        return array_filter($files, static fn (string $value): bool => strpos($value, $directory) === 0);
    }

    /**
     * Returns any files whose `basename` matches the given pattern.
     *
     * @param list<string> $files
     * @param string       $pattern Regex or pseudo-regex string
     *
     * @return list<string>
     */
    final protected static function matchFiles(array $files, string $pattern): array
    {
        // Convert pseudo-regex into their true form
        if (@preg_match($pattern, '') === false) {
            $pattern = str_replace(
                ['#', '.', '*', '?'],
                ['\#', '\.', '.*', '.'],
                $pattern
            );
            $pattern = "#{$pattern}#";
        }

        return array_filter($files, static fn ($value) => (bool) preg_match($pattern, basename($value)));
    }

    // --------------------------------------------------------------------
    // Class Core
    // --------------------------------------------------------------------

    /**
     * Loads the Filesystem helper and adds any initial files.
     *
     * @param list<string> $files
     */
    public function __construct(array $files = [])
    {
        helper(['filesystem']);

        $this->add($files)->define();
    }

    /**
     * Applies any initial inputs after the constructor.
     * This method is a stub to be implemented by child classes.
     */
    protected function define(): void
    {
    }

    /**
     * Optimizes and returns the current file list.
     *
     * @return list<string>
     */
    public function get(): array
    {
        $this->files = array_unique($this->files);
        sort($this->files, SORT_STRING);

        return $this->files;
    }

    /**
     * Sets the file list directly, files are still subject to verification.
     * This works as a "reset" method with [].
     *
     * @param list<string> $files The new file list to use
     *
     * @return $this
     */
    public function set(array $files)
    {
        $this->files = [];

        return $this->addFiles($files);
    }

    /**
     * Adds an array/single file or directory to the list.
     *
     * @param list<string>|string $paths
     *
     * @return $this
     */
    public function add($paths, bool $recursive = true)
    {
        $paths = (array) $paths;

        foreach ($paths as $path) {
            if (! is_string($path)) {
                throw new InvalidArgumentException('FileCollection paths must be strings.');
            }

            try {
                // Test for a directory
                self::resolveDirectory($path);
            } catch (FileException $e) {
                $this->addFile($path);

                continue;
            }

            $this->addDirectory($path, $recursive);
        }

        return $this;
    }

    // --------------------------------------------------------------------
    // File Handling
    // --------------------------------------------------------------------

    /**
     * Verifies and adds files to the list.
     *
     * @param list<string> $files
     *
     * @return $this
     */
    public function addFiles(array $files)
    {
        foreach ($files as $file) {
            $this->addFile($file);
        }

        return $this;
    }

    /**
     * Verifies and adds a single file to the file list.
     *
     * @return $this
     */
    public function addFile(string $file)
    {
        $this->files[] = self::resolveFile($file);

        return $this;
    }

    /**
     * Removes files from the list.
     *
     * @param list<string> $files
     *
     * @return $this
     */
    public function removeFiles(array $files)
    {
        $this->files = array_diff($this->files, $files);

        return $this;
    }

    /**
     * Removes a single file from the list.
     *
     * @return $this
     */
    public function removeFile(string $file)
    {
        return $this->removeFiles([$file]);
    }

    // --------------------------------------------------------------------
    // Directory Handling
    // --------------------------------------------------------------------

    /**
     * Verifies and adds files from each
     * directory to the list.
     *
     * @param list<string> $directories
     *
     * @return $this
     */
    public function addDirectories(array $directories, bool $recursive = false)
    {
        foreach ($directories as $directory) {
            $this->addDirectory($directory, $recursive);
        }

        return $this;
    }

    /**
     * Verifies and adds all files from a directory.
     *
     * @return $this
     */
    public function addDirectory(string $directory, bool $recursive = false)
    {
        $directory = self::resolveDirectory($directory);

        // Map the directory to depth 2 to so directories become arrays
        foreach (directory_map($directory, 2, true) as $key => $path) {
            if (is_string($path)) {
                $this->addFile($directory . $path);
            } elseif ($recursive && is_array($path)) {
                $this->addDirectory($directory . $key, true);
            }
        }

        return $this;
    }

    // --------------------------------------------------------------------
    // Filtering
    // --------------------------------------------------------------------

    /**
     * Removes any files from the list that match the supplied pattern
     * (within the optional scope).
     *
     * @param string      $pattern Regex or pseudo-regex string
     * @param string|null $scope   The directory to limit the scope
     *
     * @return $this
     */
    public function removePattern(string $pattern, ?string $scope = null)
    {
        if ($pattern === '') {
            return $this;
        }

        // Start with all files or those in scope
        $files = $scope === null ? $this->files : self::filterFiles($this->files, $scope);

        // Remove any files that match the pattern
        return $this->removeFiles(self::matchFiles($files, $pattern));
    }

    /**
     * Keeps only the files from the list that match
     * (within the optional scope).
     *
     * @param string      $pattern Regex or pseudo-regex string
     * @param string|null $scope   A directory to limit the scope
     *
     * @return $this
     */
    public function retainPattern(string $pattern, ?string $scope = null)
    {
        if ($pattern === '') {
            return $this;
        }

        // Start with all files or those in scope
        $files = $scope === null ? $this->files : self::filterFiles($this->files, $scope);

        // Matches the pattern within the scoped files and remove their inverse.
        return $this->removeFiles(array_diff($files, self::matchFiles($files, $pattern)));
    }

    // --------------------------------------------------------------------
    // Interface Methods
    // --------------------------------------------------------------------

    /**
     * Returns the current number of files in the collection.
     * Fulfills Countable.
     */
    public function count(): int
    {
        return count($this->files);
    }

    /**
     * Yields as an Iterator for the current files.
     * Fulfills IteratorAggregate.
     *
     * @return Generator<File>
     *
     * @throws FileNotFoundException
     */
    public function getIterator(): Generator
    {
        foreach ($this->get() as $file) {
            yield new File($file, true);
        }
    }
}
