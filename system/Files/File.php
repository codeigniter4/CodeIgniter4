<?php namespace CodeIgniter\Files;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2018 British Columbia Institute of Technology
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
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	2014-2018 British Columbia Institute of Technology (https://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */
use SplFileInfo;
use CodeIgniter\Files\Exceptions\FileException;
use CodeIgniter\Files\Exceptions\FileNotFoundException;

class File extends SplFileInfo
{

	/**
	 * The files size in bytes
	 *
	 * @var float
	 */
	protected $size;

	//--------------------------------------------------------------------

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

	//--------------------------------------------------------------------

	/**
	 * Retrieve the file size.
	 *
	 * Implementations SHOULD return the value stored in the "size" key of
	 * the file in the $_FILES array if available, as PHP calculates this based
	 * on the actual size transmitted.
	 *
	 * @param string $unit The unit to return:
	 *      - b   Bytes
	 *      - kb  Kilobytes
	 *      - mb  Megabytes
	 *
	 * @return int|null The file size in bytes or null if unknown.
	 */
	public function getSize(string $unit = 'b')
	{
		if (is_null($this->size))
		{
			$this->size = filesize($this->getPathname());
		}

		switch (strtolower($unit))
		{
			case 'kb':
				return number_format($this->size / 1024, 3);
			case 'mb':
				return number_format(($this->size / 1024) / 1024, 3);
		}

		return (int) $this->size;
	}

	//--------------------------------------------------------------------

	/**
	 * Attempts to determine the file extension based on the trusted
	 * getType() method. If the mime type is unknown, will return null.
	 *
	 * @return string
	 */
	public function guessExtension(): string
	{
		return \Config\Mimes::guessExtensionFromType($this->getMimeType());
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieve the media type of the file. SHOULD not use information from
	 * the $_FILES array, but should use other methods to more accurately
	 * determine the type of file, like finfo, or mime_content_type().
	 *
	 * @return string|null The media type we determined it to be.
	 */
	public function getMimeType(): string
	{
		if (function_exists('finfo_file'))
		{
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mimeType = finfo_file($finfo, $this->getRealPath());
			finfo_close($finfo);
		}
		else
		{
			$mimeType = mime_content_type($this->getRealPath());
		}

		return $mimeType;
	}

	//--------------------------------------------------------------------

	/**
	 * Generates a random names based on a simple hash and the time, with
	 * the correct file extension attached.
	 *
	 * @return string
	 */
	public function getRandomName(): string
	{
		return time() . '_' . bin2hex(random_bytes(10)) . '.' . $this->getExtension();
	}

	//--------------------------------------------------------------------

	/**
	 * Moves a file to a new location.
	 *
	 * @param string      $targetPath
	 * @param string|null $name
	 * @param bool        $overwrite
	 *
	 * @return bool
	 */
	public function move(string $targetPath, string $name = null, bool $overwrite = false)
	{
		$targetPath = rtrim($targetPath, '/') . '/';
		$name = $name ?? $this->getBaseName();
		$destination = $overwrite ? $targetPath . $name : $this->getDestination($targetPath . $name);

		if ( ! @rename($this->getPath(), $destination))
		{
			$error = error_get_last();
			throw FileException::forUnableToMove($this->getBasename(), $targetPath, strip_tags($error['message']));
		}

		@chmod($targetPath, 0777 & ~umask());

		return true;
	}

	//--------------------------------------------------------------------

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
		while (file_exists($destination))
		{
			$info = pathinfo($destination);
			if (strpos($info['filename'], $delimiter) !== false)
			{
				$parts = explode($delimiter, $info['filename']);
				if (is_numeric(end($parts)))
				{
					$i = end($parts);
					array_pop($parts);
					array_push($parts, ++ $i);
					$destination = $info['dirname'] . '/' . implode($delimiter, $parts) . '.' . $info['extension'];
				}
				else
				{
					$destination = $info['dirname'] . '/' . $info['filename'] . $delimiter . ++ $i . '.' . $info['extension'];
				}
			}
			else
			{
				$destination = $info['dirname'] . '/' . $info['filename'] . $delimiter . ++ $i . '.' . $info['extension'];
			}
		}
		return $destination;
	}

	//--------------------------------------------------------------------
}
