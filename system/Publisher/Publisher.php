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

namespace CodeIgniter\Publisher;

use CodeIgniter\Autoloader\FileLocatorInterface;
use CodeIgniter\Files\FileCollection;
use CodeIgniter\HTTP\URI;
use CodeIgniter\Publisher\Exceptions\PublisherException;
use Config\Publisher as PublisherConfig;
use RuntimeException;
use Throwable;

/**
 * Publishers read in file paths from a variety of sources and copy
 * the files out to different destinations. This class acts both as
 * a base for individual publication directives as well as the mode
 * of discovery for said instances. In this class a "file" is a full
 * path to a verified file while a "path" is relative to its source
 * or destination and may indicate either a file or directory of
 * unconfirmed existence.
 *
 * Class failures throw the PublisherException, but some underlying
 * methods may percolate different exceptions, like FileException,
 * FileNotFoundException or InvalidArgumentException.
 *
 * Write operations will catch all errors in the file-specific
 * $errors property to minimize impact of partial batch operations.
 */
class Publisher extends FileCollection
{
    /**
     * Array of discovered Publishers.
     *
     * @var array<string, list<self>|null>
     */
    private static array $discovered = [];

    /**
     * Directory to use for methods that need temporary storage.
     * Created on-the-fly as needed.
     */
    private ?string $scratch = null;

    /**
     * Exceptions for specific files from the last write operation.
     *
     * @var array<string, Throwable>
     */
    private array $errors = [];

    /**
     * List of file published curing the last write operation.
     *
     * @var list<string>
     */
    private array $published = [];

    /**
     * List of allowed directories and their allowed files regex.
     * Restrictions are intentionally private to prevent overriding.
     *
     * @var array<string,string>
     */
    private readonly array $restrictions;

    private readonly ContentReplacer $replacer;

    /**
     * Base path to use for the source.
     *
     * @var string
     */
    protected $source = ROOTPATH;

    /**
     * Base path to use for the destination.
     *
     * @var string
     */
    protected $destination = FCPATH;

    // --------------------------------------------------------------------
    // Support Methods
    // --------------------------------------------------------------------

    /**
     * Discovers and returns all Publishers in the specified namespace directory.
     *
     * @return list<self>
     */
    final public static function discover(string $directory = 'Publishers'): array
    {
        if (isset(self::$discovered[$directory])) {
            return self::$discovered[$directory];
        }

        self::$discovered[$directory] = [];

        /** @var FileLocatorInterface $locator */
        $locator = service('locator');

        if ([] === $files = $locator->listFiles($directory)) {
            return [];
        }

        // Loop over each file checking to see if it is a Publisher
        foreach (array_unique($files) as $file) {
            $className = $locator->findQualifiedNameFromPath($file);

            if ($className !== false && class_exists($className) && is_a($className, self::class, true)) {
                self::$discovered[$directory][] = new $className();
            }
        }

        sort(self::$discovered[$directory]);

        return self::$discovered[$directory];
    }

    /**
     * Removes a directory and all its files and subdirectories.
     */
    private static function wipeDirectory(string $directory): void
    {
        if (is_dir($directory)) {
            // Try a few times in case of lingering locks
            $attempts = 10;

            while ((bool) $attempts && ! delete_files($directory, true, false, true)) {
                // @codeCoverageIgnoreStart
                $attempts--;
                usleep(100000); // .1s
                // @codeCoverageIgnoreEnd
            }

            @rmdir($directory);
        }
    }

    // --------------------------------------------------------------------
    // Class Core
    // --------------------------------------------------------------------

    /**
     * Loads the helper and verifies the source and destination directories.
     */
    public function __construct(?string $source = null, ?string $destination = null)
    {
        helper(['filesystem']);

        $this->source      = self::resolveDirectory($source ?? $this->source);
        $this->destination = self::resolveDirectory($destination ?? $this->destination);

        $this->replacer = new ContentReplacer();

        // Restrictions are intentionally not injected to prevent overriding
        $this->restrictions = config(PublisherConfig::class)->restrictions;

        // Make sure the destination is allowed
        foreach (array_keys($this->restrictions) as $directory) {
            if (str_starts_with($this->destination, $directory)) {
                return;
            }
        }

        throw PublisherException::forDestinationNotAllowed($this->destination);
    }

    /**
     * Cleans up any temporary files in the scratch space.
     */
    public function __destruct()
    {
        if (isset($this->scratch)) {
            self::wipeDirectory($this->scratch);

            $this->scratch = null;
        }
    }

    /**
     * Reads files from the sources and copies them out to their destinations.
     * This method should be reimplemented by child classes intended for
     * discovery.
     *
     * @throws RuntimeException
     */
    public function publish(): bool
    {
        // Safeguard against accidental misuse
        if ($this->source === ROOTPATH && $this->destination === FCPATH) {
            throw new RuntimeException('Child classes of Publisher should provide their own publish method or a source and destination.');
        }

        return $this->addPath('/')->merge(true);
    }

    // --------------------------------------------------------------------
    // Property Accessors
    // --------------------------------------------------------------------

    /**
     * Returns the source directory.
     */
    final public function getSource(): string
    {
        return $this->source;
    }

    /**
     * Returns the destination directory.
     */
    final public function getDestination(): string
    {
        return $this->destination;
    }

    /**
     * Returns the temporary workspace, creating it if necessary.
     */
    final public function getScratch(): string
    {
        if ($this->scratch === null) {
            $this->scratch = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . bin2hex(random_bytes(6)) . DIRECTORY_SEPARATOR;
            mkdir($this->scratch, 0700);
            $this->scratch = realpath($this->scratch) ? realpath($this->scratch) . DIRECTORY_SEPARATOR
                : $this->scratch;
        }

        return $this->scratch;
    }

    /**
     * Returns errors from the last write operation if any.
     *
     * @return array<string,Throwable>
     */
    final public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Returns the files published by the last write operation.
     *
     * @return list<string>
     */
    final public function getPublished(): array
    {
        return $this->published;
    }

    // --------------------------------------------------------------------
    // Additional Handlers
    // --------------------------------------------------------------------

    /**
     * Verifies and adds paths to the list.
     *
     * @param list<string> $paths
     *
     * @return $this
     */
    final public function addPaths(array $paths, bool $recursive = true)
    {
        foreach ($paths as $path) {
            $this->addPath($path, $recursive);
        }

        return $this;
    }

    /**
     * Adds a single path to the file list.
     *
     * @return $this
     */
    final public function addPath(string $path, bool $recursive = true)
    {
        $this->add($this->source . $path, $recursive);

        return $this;
    }

    /**
     * Downloads and stages files from an array of URIs.
     *
     * @param list<string> $uris
     *
     * @return $this
     */
    final public function addUris(array $uris)
    {
        foreach ($uris as $uri) {
            $this->addUri($uri);
        }

        return $this;
    }

    /**
     * Downloads a file from the URI, and adds it to the file list.
     *
     * @param string $uri Because HTTP\URI is stringable it will still be accepted
     *
     * @return $this
     */
    final public function addUri(string $uri)
    {
        // Figure out a good filename (using URI strips queries and fragments)
        $file = $this->getScratch() . basename((new URI($uri))->getPath());

        // Get the content and write it to the scratch space
        write_file($file, service('curlrequest')->get($uri)->getBody());

        return $this->addFile($file);
    }

    // --------------------------------------------------------------------
    // Write Methods
    // --------------------------------------------------------------------

    /**
     * Removes the destination and all its files and folders.
     *
     * @return $this
     */
    final public function wipe()
    {
        self::wipeDirectory($this->destination);

        return $this;
    }

    /**
     * Copies all files into the destination, does not create directory structure.
     *
     * @param bool $replace Whether to overwrite existing files.
     *
     * @return bool Whether all files were copied successfully
     */
    final public function copy(bool $replace = true): bool
    {
        $this->errors = $this->published = [];

        foreach ($this->get() as $file) {
            $to = $this->destination . basename($file);

            try {
                $this->safeCopyFile($file, $to, $replace);
                $this->published[] = $to;
            } catch (Throwable $e) {
                $this->errors[$file] = $e;
            }
        }

        return $this->errors === [];
    }

    /**
     * Merges all files into the destination.
     * Creates a mirrored directory structure only for files from source.
     *
     * @param bool $replace Whether to overwrite existing files.
     *
     * @return bool Whether all files were copied successfully
     */
    final public function merge(bool $replace = true): bool
    {
        $this->errors = $this->published = [];

        // Get the files from source for special handling
        $sourced = self::filterFiles($this->get(), $this->source);

        // Handle everything else with a flat copy
        $this->files = array_diff($this->files, $sourced);
        $this->copy($replace);

        // Copy each sourced file to its relative destination
        foreach ($sourced as $file) {
            // Resolve the destination path
            $to = $this->destination . substr($file, strlen($this->source));

            try {
                $this->safeCopyFile($file, $to, $replace);
                $this->published[] = $to;
            } catch (Throwable $e) {
                $this->errors[$file] = $e;
            }
        }

        return $this->errors === [];
    }

    /**
     * Replace content
     *
     * @param array $replaces [search => replace]
     */
    public function replace(string $file, array $replaces): bool
    {
        $this->verifyAllowed($file, $file);

        $content = file_get_contents($file);

        $newContent = $this->replacer->replace($content, $replaces);

        $return = file_put_contents($file, $newContent);

        return $return !== false;
    }

    /**
     * Add line after the line with the string
     *
     * @param string $after String to search.
     */
    public function addLineAfter(string $file, string $line, string $after): bool
    {
        $this->verifyAllowed($file, $file);

        $content = file_get_contents($file);

        $result = $this->replacer->addAfter($content, $line, $after);

        if ($result !== null) {
            $return = file_put_contents($file, $result);

            return $return !== false;
        }

        return false;
    }

    /**
     * Add line before the line with the string
     *
     * @param string $before String to search.
     */
    public function addLineBefore(string $file, string $line, string $before): bool
    {
        $this->verifyAllowed($file, $file);

        $content = file_get_contents($file);

        $result = $this->replacer->addBefore($content, $line, $before);

        if ($result !== null) {
            $return = file_put_contents($file, $result);

            return $return !== false;
        }

        return false;
    }

    /**
     * Verify this is an allowed file for its destination.
     */
    private function verifyAllowed(string $from, string $to): void
    {
        // Verify this is an allowed file for its destination
        foreach ($this->restrictions as $directory => $pattern) {
            if (str_starts_with($to, $directory) && self::matchFiles([$to], $pattern) === []) {
                throw PublisherException::forFileNotAllowed($from, $directory, $pattern);
            }
        }
    }

    /**
     * Copies a file with directory creation and identical file awareness.
     * Intentionally allows errors.
     *
     * @throws PublisherException For collisions and restriction violations
     */
    private function safeCopyFile(string $from, string $to, bool $replace): void
    {
        // Verify this is an allowed file for its destination
        $this->verifyAllowed($from, $to);

        // Check for an existing file
        if (file_exists($to)) {
            // If not replacing or if files are identical then consider successful
            if (! $replace || same_file($from, $to)) {
                return;
            }

            // If it is a directory then do not try to remove it
            if (is_dir($to)) {
                throw PublisherException::forCollision($from, $to);
            }

            // Try to remove anything else
            unlink($to);
        }

        // Make sure the directory exists
        if (! is_dir($directory = pathinfo($to, PATHINFO_DIRNAME))) {
            mkdir($directory, 0775, true);
        }

        // Allow copy() to throw errors
        copy($from, $to);
    }
}
