<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Publisher;

use CodeIgniter\Autoloader\FileLocator;
use CodeIgniter\Files\File;
use CodeIgniter\HTTP\URI;
use CodeIgniter\Publisher\Exceptions\PublisherException;
use RuntimeException;
use Throwable;

/**
 * Publisher Class
 *
 * Publishers read in file paths from a variety of sources and copy
 * the files out to different destinations. This class acts both as
 * a base for individual publication directives as well as the mode
 * of discovery for said instances. In this class a "file" is a full
 * path to a verified file while a "path" is relative to its source
 * or destination and may indicate either a file or directory of
 * unconfirmed existence.
 * class failures throw the PublisherException, but some underlying
 * methods may percolate different exceptions, like FileException,
 * FileNotFoundException or InvalidArgumentException.
 * Write operations will catch all errors in the file-specific
 * $errors property to minimize impact of partial batch operations.
 */
class Publisher
{
	/**
	 * Array of discovered Publishers.
	 *
	 * @var array<string,self[]|null>
	 */
	private static $discovered = [];

	/**
	 * Directory to use for methods that need temporary storage.
	 * Created on-the-fly as needed.
	 *
	 * @var string|null
	 */
	private $scratch;

	/**
	 * The current list of files.
	 *
	 * @var string[]
	 */
	private $files = [];

	/**
	 * Exceptions for specific files from the last write operation.
	 *
	 * @var array<string,Throwable>
	 */
	private $errors = [];

	/**
	 * List of file published curing the last write operation.
	 *
	 * @var string[]
	 */
	private $published = [];

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

	/**
	 * Discovers and returns all Publishers in the specified namespace directory.
	 *
	 * @param string $directory
	 *
	 * @return self[]
	 */
	final public static function discover(string $directory = 'Publishers'): array
	{
		if (isset(self::$discovered[$directory]))
		{
			return self::$discovered[$directory];
		}
	
		self::$discovered[$directory] = [];

		/** @var FileLocator $locator */
		$locator = service('locator');

		if ([] === $files = $locator->listFiles($directory))
		{
			return [];
		}

		// Loop over each file checking to see if it is a Publisher
		foreach (array_unique($files) as $file)
		{
			$className = $locator->findQualifiedNameFromPath($file);

			if (is_string($className) && class_exists($className) && is_a($className, self::class, true))
			{
				self::$discovered[$directory][] = new $className();
			}
		}

		sort(self::$discovered[$directory]);

		return self::$discovered[$directory];
	}

	//--------------------------------------------------------------------

	/**
	 * Resolves a full path and verifies it is an actual directory.
	 *
	 * @param string $directory
	 *
	 * @return string
	 */
	private static function resolveDirectory(string $directory): string
	{
		if (! is_dir($directory = set_realpath($directory)))
		{
			$caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
			throw PublisherException::forExpectedDirectory($caller['function']);
		}

		return $directory;
	}

	/**
	 * Resolves a full path and verifies it is an actual file.
	 *
	 * @param string $file
	 *
	 * @return string
	 */
	private static function resolveFile(string $file): string
	{
		if (! is_file($file = set_realpath($file)))
		{
			$caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
			throw PublisherException::forExpectedFile($caller['function']);
		}

		return $file;
	}

	//--------------------------------------------------------------------

	/**
	 * Removes files that are not part of the given directory (recursive).
	 *
	 * @param string[] $files
	 * @param string   $directory
	 *
	 * @return string[]
	 */
	private static function filterFiles(array $files, string $directory): array
	{
		$directory = self::resolveDirectory($directory);

		return array_filter($files, function ($value) use ($directory) {
			return strpos($value, $directory) === 0;
		});
	}

	/**
	 * Returns any files whose `basename` matches the given pattern.
	 *
	 * @param array<string> $files
	 * @param string        $pattern Regex or pseudo-regex string
	 *
	 * @return string[]
	 */
	private static function matchFiles(array $files, string $pattern)
	{
		// Convert pseudo-regex into their true form
		if (@preg_match($pattern, null) === false) // @phpstan-ignore-line
		{
			$pattern = str_replace(
				['#', '.', '*', '?'],
				['\#', '\.', '.*', '.'],
				$pattern
			);
			$pattern = "#{$pattern}#";
		}

		return array_filter($files, function ($value) use ($pattern) {
			return (bool) preg_match($pattern, basename($value));
		});
	}

	//--------------------------------------------------------------------

	/*
	 * Removes a directory and all its files and subdirectories.
	 *
	 * @param string $directory
	 *
	 * @return void
	 */
	private static function wipeDirectory(string $directory): void
	{
		if (is_dir($directory))
		{
			// Try a few times in case of lingering locks
			$attempts = 10;
			while ((bool) $attempts && ! delete_files($directory, true, false, true))
			{
				// @codeCoverageIgnoreStart
				$attempts--;
				usleep(100000); // .1s
				// @codeCoverageIgnoreEnd
			}

			@rmdir($directory);
		}
	}

	/**
	 * Copies a file with directory creation and identical file awareness.
	 * Intentionally allows errors.
	 *
	 * @param string  $from
	 * @param string  $to
	 * @param boolean $replace
	 *
	 * @return void
	 *
	 * @throws PublisherException For unresolvable collisions
	 */
	private static function safeCopyFile(string $from, string $to, bool $replace): void
	{
		// Check for an existing file
		if (file_exists($to))
		{
			// If not replacing or if files are identical then consider successful
			if (! $replace || same_file($from, $to))
			{
				return;
			}

			// If it is a directory then do not try to remove it
			if (is_dir($to))
			{
				throw PublisherException::forCollision($from, $to);
			}

			// Try to remove anything else
			unlink($to);
		}

		// Make sure the directory exists
		if (! is_dir($directory = pathinfo($to, PATHINFO_DIRNAME)))
		{
			mkdir($directory, 0775, true);
		}

		// Allow copy() to throw errors
		copy($from, $to);
	}

	//--------------------------------------------------------------------

	/**
	 * Loads the helper and verifies the source and destination directories.
	 *
	 * @param string|null $source
	 * @param string|null $destination
	 */
	public function __construct(string $source = null, string $destination = null)
	{
		helper(['filesystem']);

		$this->source      = self::resolveDirectory($source ?? $this->source);
		$this->destination = self::resolveDirectory($destination ?? $this->destination);
	}

	/**
	 * Cleans up any temporary files in the scratch space.
	 */
	public function __destruct()
	{
		if (isset($this->scratch))
		{
			self::wipeDirectory($this->scratch);

			$this->scratch = null;
		}
	}

	/**
	 * Reads files from the sources and copies them out to their destinations.
	 * This method should be reimplemented by child classes intended for
	 * discovery.
	 *
	 * @return boolean
	 */
	public function publish(): bool
	{
		if ($this->source === ROOTPATH && $this->destination === FCPATH)
		{
			throw new RuntimeException('Child classes of Publisher should provide their own source and destination or publish method.');
		}

		return $this->addPath('/')->merge(true);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the source directory.
	 *
	 * @return string
	 */
	final public function getSource(): string
	{
		return $this->source;
	}

	/**
	 * Returns the destination directory.
	 *
	 * @return string
	 */
	final public function getDestination(): string
	{
		return $this->destination;
	}

	/**
	 * Returns the temporary workspace, creating it if necessary.
	 *
	 * @return string
	 */
	final public function getScratch(): string
	{
		if (is_null($this->scratch))
		{
			$this->scratch = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . bin2hex(random_bytes(6)) . DIRECTORY_SEPARATOR;
			mkdir($this->scratch, 0700);
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
	 * @return string[]
	 */
	final public function getPublished(): array
	{
		return $this->published;
	}

	/**
	 * Optimizes and returns the current file list.
	 *
	 * @return string[]
	 */
	final public function getFiles(): array
	{
		$this->files = array_unique($this->files, SORT_STRING);
		sort($this->files, SORT_STRING);

		return $this->files;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the file list directly, files are still subject to verification.
	 * This works as a "reset" method with [].
	 *
	 * @param string[] $files The new file list to use
	 *
	 * @return $this
	 */
	final public function setFiles(array $files)
	{
		$this->files = [];

		return $this->addFiles($files);
	}

	/**
	 * Verifies and adds files to the list.
	 *
	 * @param string[] $files
	 *
	 * @return $this
	 */
	final public function addFiles(array $files)
	{
		foreach ($files as $file)
		{
			$this->addFile($file);
		}

		return $this;
	}

	/**
	 * Verifies and adds a single file to the file list.
	 *
	 * @param string $file
	 *
	 * @return $this
	 */
	final public function addFile(string $file)
	{
		$this->files[] = self::resolveFile($file);

		return $this;
	}

	/**
	 * Removes files from the list.
	 *
	 * @param string[] $files
	 *
	 * @return $this
	 */
	final public function removeFiles(array $files)
	{
		$this->files = array_diff($this->files, $files);

		return $this;
	}

	/**
	 * Removes a single file from the list.
	 *
	 * @param string $file
	 *
	 * @return $this
	 */
	final public function removeFile(string $file)
	{
		return $this->removeFiles([$file]);
	}

	//--------------------------------------------------------------------

	/**
	 * Verifies and adds files from each
	 * directory to the list.
	 *
	 * @param string[] $directories
	 * @param bool $recursive
	 *
	 * @return $this
	 */
	final public function addDirectories(array $directories, bool $recursive = false)
	{
		foreach ($directories as $directory)
		{
			$this->addDirectory($directory, $recursive);
		}

		return $this;
	}

	/**
	 * Verifies and adds all files from a directory.
	 *
	 * @param string  $directory
	 * @param boolean $recursive
	 *
	 * @return $this
	 */
	final public function addDirectory(string $directory, bool $recursive = false)
	{
		$directory = self::resolveDirectory($directory);

		// Map the directory to depth 2 to so directories become arrays
		foreach (directory_map($directory, 2, true) as $key => $path)
		{
			if (is_string($path))
			{
				$this->addFile($directory . $path);
			}
			elseif ($recursive && is_array($path))
			{
				$this->addDirectory($directory . $key, true);
			}
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Verifies and adds paths to the list.
	 *
	 * @param string[] $paths
	 * @param bool $recursive
	 *
	 * @return $this
	 */
	final public function addPaths(array $paths, bool $recursive = true)
	{
		foreach ($paths as $path)
		{
			$this->addPath($path, $recursive);
		}

		return $this;
	}

	/**
	 * Adds a single path to the file list.
	 *
	 * @param string  $path
	 * @param boolean $recursive
	 *
	 * @return $this
	 */
	final public function addPath(string $path, bool $recursive = true)
	{
		$full = $this->source . $path;

		// Test for a directory
		try
		{
			$directory = self::resolveDirectory($full);
		}
		catch (PublisherException $e)
		{
			return $this->addFile($full);
		}

		return $this->addDirectory($full, $recursive);
	}

	//--------------------------------------------------------------------

	/**
	 * Downloads and stages files from an array of URIs.
	 *
	 * @param string[] $uris
	 *
	 * @return $this
	 */
	final public function addUris(array $uris)
	{
		foreach ($uris as $uri)
		{
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

	//--------------------------------------------------------------------

	/**
	 * Removes any files from the list that match the supplied pattern
	 * (within the optional scope).
	 *
	 * @param string      $pattern Regex or pseudo-regex string
	 * @param string|null $scope The directory to limit the scope
	 *
	 * @return $this
	 */
	final public function removePattern(string $pattern, string $scope = null)
	{
		if ($pattern === '')
		{
			return $this;
		}

		// Start with all files or those in scope
		$files = is_null($scope) ? $this->files : self::filterFiles($this->files, $scope);

		// Remove any files that match the pattern
		return $this->removeFiles(self::matchFiles($files, $pattern));
	}

	/**
	 * Keeps only the files from the list that match
	 * (within the optional scope).
	 *
	 * @param string      $pattern Regex or pseudo-regex string
	 * @param string|null $scope A directory to limit the scope
	 *
	 * @return $this
	 */
	final public function retainPattern(string $pattern, string $scope = null)
	{
		if ($pattern === '')
		{
			return $this;
		}

		// Start with all files or those in scope
		$files = is_null($scope) ? $this->files : self::filterFiles($this->files, $scope);

		// Matches the pattern within the scoped files and remove their inverse.
		return $this->removeFiles(array_diff($files, self::matchFiles($files, $pattern)));
	}

	//--------------------------------------------------------------------

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

	//--------------------------------------------------------------------

	/**
	 * Copies all files into the destination, does not create directory structure.
	 *
	 * @param boolean $replace Whether to overwrite existing files.
	 *
	 * @return boolean Whether all files were copied successfully
	 */
	final public function copy(bool $replace = true): bool
	{
		$this->errors = $this->published = [];

		foreach ($this->getFiles() as $file)
		{
			$to = $this->destination . basename($file);

			try
			{
				self::safeCopyFile($file, $to, $replace);
				$this->published[] = $to;
			}
			catch (Throwable $e)
			{
				$this->errors[$file] = $e;
			}
		}

		return $this->errors === [];
	}

	/**
	 * Merges all files into the destination.
	 * Creates a mirrored directory structure only for files from source.
	 *
	 * @param boolean $replace Whether to overwrite existing files.
	 *
	 * @return boolean Whether all files were copied successfully
	 */
	final public function merge(bool $replace = true): bool
	{
		$this->errors = $this->published = [];

		// Get the file from source for special handling
		$sourced = self::filterFiles($this->getFiles(), $this->source);

		// Handle everything else with a flat copy
		$this->files = array_diff($this->files, $sourced);
		$this->copy($replace);

		// Copy each sourced file to its relative destination
		foreach ($sourced as $file)
		{
			// Resolve the destination path
			$to = $this->destination . substr($file, strlen($this->source));

			try
			{
				self::safeCopyFile($file, $to, $replace);
				$this->published[] = $to;
			}
			catch (Throwable $e)
			{
				$this->errors[$file] = $e;
			}
		}

		return $this->errors === [];
	}
}
