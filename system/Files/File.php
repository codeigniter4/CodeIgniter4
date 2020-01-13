<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019 CodeIgniter Foundation
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2019 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Files;

use SplFileInfo;
use CodeIgniter\Files\Exceptions\FileException;
use CodeIgniter\Files\Exceptions\FileNotFoundException;
use Config\Mimes;

/**
 * Wrapper for PHP's built-in SplFileInfo, with goodies.
 *
 * @package CodeIgniter\Files
 */
class File extends SplFileInfo
{
	/**
	 * Size units ('b' - byte, 'kb' - kilobytes, 'mb' - megabytes).
	 * 
	 * @var array
	 */
	protected const SIZE_UNITS = [
		'b' => 1,
		'kb' => 1024,
		'mb' => ‭1048576‬,
	];

	/**
	 * The files size in bytes.
	 *
	 * @var int
	 */
	protected $size;

	/**
	 * MIME content type by default.
	 * 
	 * @var string
	 */
	protected $originalMimeType;

	/**
	 * MIME content type.
	 * 
	 * @var string
	 */
	protected $mimeType;

	/**
	 * Run our SplFileInfo constructor with an optional verification
	 * that the path is really a file.
	 *
	 * @param string $path
	 * @param bool   $checkFile
	 */
	public function __construct(string $path, bool $checkFile = false)
	{
		if ($checkFile && ! is_file($path))
		{
			throw FileNotFoundException::forFileNotFound($path);
		}

		parent::__construct($path);
	}

	/**
	 * Retrieve the file size in bytes.
	 *
	 * @return int
	 */
	public function getSize(): int
	{
		if ($this->size === null)
		{
			$this->size = parent::getSize();
			if (! $this->size && $this->getPathname())
			{
				$this->size = @filesize($this->getPathname()) ?: 0;
			}
		}

		return $this->size;
	}

	/**
	 * Retrieve the file size by
	 *
	 * @param string $unit The unit to return.
	 * @param int $precision The optional number of decimal digits to round to.
	 *
	 * @return float|null
	 */
	public function getSizeByUnit(string $unit = 'b', int $precision = 3): ?float
	{
		if (! $this->getSize())
		{
			return null;
		}
		$unit = strtolower($unit);
		if (! isset(static::SIZE_UNITS[$unit]))
		{
			throw new InvalidArgumentException('Wrong unit');
		}
		return round($this->getSize() / static::SIZE_UNITS[$unit], max(0, $precision));
	}

	/**
	 * Attempts to determine the file extension based on the trusted
	 * getMimeType() method. If the mime type is unknown, will return null.
	 *
	 * @return string|null
	 */
	public function guessExtension(): ?string
	{
		return Mimes::guessExtensionFromType($this->getMimeType());
	}

	/**
	 * Retrieve the media type of the file. SHOULD not use information from
	 * the $_FILES array, but should use other methods to more accurately
	 * determine the type of file, like finfo, or mime_content_type().
	 *
	 * @return string The media type we determined it to be.
	 */
	public function getMimeType(): string
	{
		if (! $this->mimeType)
		{
			if (function_exists('mime_content_type') && $this->getRealPath())
			{
				$this->mimeType = @mime_content_type($this->getRealPath()) ?: null;
			}
			else
			{
				$this->mimeType = Mimes::guessTypeFromExtension($this->getExtension());
			}
		}
		return $this->mimeType ?? ($this->originalMimeType ?? 'application/octet-stream');
	}

	/**
	 * Generates a random names based on a simple hash and the time, with
	 * the correct file extension attached.
	 *
	 * @return string
	 */
	public function getRandomName(): string
	{
		$name = time() . '_' . bin2hex(random_bytes(10));
		if ($this->getExtension())
		{
			$name .= '.' . $this->getExtension();
		}
		return $name;
	}

	/**
	 * Moves a file to a new location.
	 *
	 * @param string      $targetPath
	 * @param string|null $name
	 * @param bool        $overwrite
	 *
	 * @return self
	 */
	public function move(string $targetPath, ?string $name = null, bool $overwrite = false)
	{
		$targetPath = realpath($targetPath);
		@chmod($targetPath, 0777 & ~umask());

		$destination = $targetPath . DIRECTORY_SEPARATOR . ($name ?? $this->getBasename());
		if (! $overwrite)
		{
			$destination = $this->getDestination($destination);
		}

		$oldName = $this->getRealPath() ?: $this->getPath();
		if (! @rename($oldName, $destination))
		{
			$error = error_get_last();
			throw FileException::forUnableToMove($this->getBasename(), $targetPath, strip_tags($error['message']));
		}

		return new self($destination);
	}

	/**
	 * Returns the destination path for the move operation where overwriting is not expected.
	 *
	 * First, it checks whether the delimiter is present in the filename, if it is, then it checks whether the
	 * last element is an integer as there may be cases that the delimiter may be present in the filename.
	 * For the all other cases, it appends an integer starting from zero before the file's extension.
	 *
	 * @param string $destination
	 * @param string $delimiter
	 * @param int    $i
	 *
	 * @return string
	 */
	public function getDestination(string $destination, string $delimiter = '_', int $i = 0): string
	{
		while (is_file($destination))
		{
			$info = pathinfo($destination);
			$destination = ($info['dirname'] ?? '') . DIRECTORY_SEPARATOR;
			if (strpos($info['filename'], $delimiter) !== false)
			{
				$parts = explode($delimiter, $info['filename']);
				if (is_numeric(end($parts)))
				{
					$parts[key($parts)]++;
					$destination .= implode($delimiter, $parts);
				}
				else
				{
					$destination .= $info['filename'] . $delimiter . ++$i;
				}
			}
			else
			{
				$destination .= $info['filename'] . $delimiter . ++$i;
			}
			$destination .= isset($info['extension']) ? '.' . $info['extension'] : '';
		}

		return $destination;
	}
}
