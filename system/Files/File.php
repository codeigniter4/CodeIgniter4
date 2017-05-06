<?php namespace CodeIgniter\Files;

use SPLFileInfo;

require_once __DIR__.'/Exceptions.php';

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
	 * Run our SPLFileInfo constructor with an optional verification
	 * that the path is really a file.
	 *
	 * @param string $path
	 * @param bool   $checkFile
	 */
	public function __construct(string $path, bool $checkFile = true)
	{
		if ($checkFile && ! is_file($path))
		{
			throw new FileNotFoundException();
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
	public function getSize(string $unit='b')
	{
		if (is_null($this->size))
		{
			$this->size = filesize($this->getPathname());
		}

		switch (strtolower($unit))
		{
			case 'kb':
				return number_format($this->size / 1024, 3);
				break;
			case 'mb':
				return number_format(($this->size / 1024) / 1024, 3);
				break;
		}

		return $this->size;
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
			$finfo      = finfo_open(FILEINFO_MIME_TYPE);
			$mimeType   = finfo_file($finfo, $this->getPath());
			finfo_close($finfo);
		}
		else
		{
			$mimeType = mime_content_type($this->getPath());
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
		return time().'_'.bin2hex(random_bytes(10)).'.'.$this->getExtension();
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
		$targetPath = rtrim($targetPath, '/').'/';
		$name = $name ?? $this->getBaseName();
		$destination = $overwrite
			? $this->getDestination($targetPath.$name)
			: $targetPath.$name;

		if (! @rename($this->getPath(), $destination))
		{
			$error = error_get_last();
			throw new \RuntimeException(sprintf('Could not move file %s to %s (%s)', $this->getBasename(),$targetPath, strip_tags($error['message'])));
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
					array_push($parts, ++$i);
					$destination = $info['dirname'] . '/' . implode($delimiter, $parts) . '.' .  $info['extension'];
				}
				else
				{
					$destination = $info['dirname'] . '/' . $info['filename'] . $delimiter . ++$i . '.' .  $info['extension'];
				}
			}
			else
			{
				$destination = $info['dirname'] . '/' . $info['filename'] . $delimiter . ++$i . '.' .  $info['extension'];
			}
		}
		return $destination;
	}

	//--------------------------------------------------------------------

}
