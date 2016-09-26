<?php namespace CodeIgniter\HTTP\Files;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
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
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */

/**
 * Value object representing a single file uploaded through an
 * HTTP request. Used by the IncomingRequest class to
 * provide files.
 *
 * Typically, implementors will extend the SplFileInfo class.
 *
 * @package CodeIgniter\HTTP
 */
class UploadedFile implements UploadedFileInterface
{
	/**
	 * The path to the temporary file.
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * The original filename as provided by the client.
	 *
	 * @var string
	 */
	protected $originalName;

	/**
	 * The filename given to a file during a move.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The type of file as provided by PHP
	 *
	 * @var string
	 */
	protected $originalMimeType;

	/**
	 * The type of file based on
	 * our inspections.
	 *
	 * @var string
	 */
	protected $mimeType;

	/**
	 * The files size in bytes
	 *
	 * @var int
	 */
	protected $size;

	/**
	 * The error constant of the upload
	 * (one of PHP's UPLOADERRXXX constants)
	 *
	 * @var int
	 */
	protected $error;

	/**
	 * Whether the file has been moved already or not.
	 *
	 * @var bool
	 */
	protected $hasMoved = false;

	//--------------------------------------------------------------------

	/**
	 * Accepts the file information as would be filled in from the $_FILES array.
	 *
	 * @param string $path         The temporary location of the uploaded file.
	 * @param string $originalName The client-provided filename.
	 * @param string $mimeType     The type of file as provided by PHP
	 * @param int    $size         The size of the file, in bytes
	 * @param int    $error        The error constant of the upload (one of PHP's UPLOADERRXXX constants)
	 */
	public function __construct(string $path, string $originalName, string $mimeType = null, int $size = null, int $error = null)
	{
		$this->path             = $path;
		$this->name             = $originalName;
		$this->originalName     = $originalName;
		$this->originalMimeType = $mimeType;
		$this->size             = $size;
		$this->error            = $error;
	}

	//--------------------------------------------------------------------

	/**
	 * Move the uploaded file to a new location.
	 *
	 * $targetPath may be an absolute path, or a relative path. If it is a
	 * relative path, resolution should be the same as used by PHP's rename()
	 * function.
	 *
	 * The original file MUST be removed on completion.
	 *
	 * If this method is called more than once, any subsequent calls MUST raise
	 * an exception.
	 *
	 * When used in an SAPI environment where $_FILES is populated, when writing
	 * files via moveTo(), is_uploaded_file() and move_uploaded_file() SHOULD be
	 * used to ensure permissions and upload status are verified correctly.
	 *
	 * If you wish to move to a stream, use getStream(), as SAPI operations
	 * cannot guarantee writing to stream destinations.
	 *
	 * @see http://php.net/is_uploaded_file
	 * @see http://php.net/move_uploaded_file
	 *
	 * @param string $targetPath Path to which to move the uploaded file.
	 * @param string $name       the name to rename the file to.
	 *
	 * @throws \InvalidArgumentException if the $path specified is invalid.
	 * @throws \RuntimeException on any error during the move operation.
	 * @throws \RuntimeException on the second or subsequent call to the method.
	 */
	public function move(string $targetPath, string $name = null)
	{
		if ($this->hasMoved)
		{
			throw new \RuntimeException('The file has already been moved.');
		}

		if (! $this->isValid())
		{
			throw new \RuntimeException('The original file is not a valid file.');
		}

		$targetPath = rtrim($targetPath, '/').'/';
		$name = is_null($name) ? $this->getName() : $name;

		if (! @move_uploaded_file($this->path, $targetPath.$name))
		{
			$error = error_get_last();
			throw new \RuntimeException(sprintf('Could not move file %s to %s (%s)', basename($this->path), $targetPath, strip_tags($error['message'])));
		}

		@chmod($targetPath, 0777 & ~umask());

		// Success, so store our new information
		$this->path = $targetPath;
		$this->name = $name;
		$this->hasMoved = true;

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns whether the file has been moved or not. If it has,
	 * the move() method will not work and certain properties, like
	 * the tempName, will no longer be available.
	 *
	 * @return bool
	 */
	public function hasMoved(): bool
	{
		return $this->hasMoved;
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
	public function getSize(string $unit='b'): int
	{
		if (is_null($this->size))
		{
			$this->size = filesize($this->path);
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
	 * Retrieve the error associated with the uploaded file.
	 *
	 * The return value MUST be one of PHP's UPLOAD_ERR_XXX constants.
	 *
	 * If the file was uploaded successfully, this method MUST return
	 * UPLOAD_ERR_OK.
	 *
	 * Implementations SHOULD return the value stored in the "error" key of
	 * the file in the $_FILES array.
	 *
	 * @see http://php.net/manual/en/features.file-upload.errors.php
	 * @return int One of PHP's UPLOAD_ERR_XXX constants.
	 */
	public function getError(): int
	{
		if (is_null($this->error))
		{
			return UPLOAD_ERR_OK;
		}

		return $this->error;
	}

	//--------------------------------------------------------------------

	/**
	 * Get error string
	 *
	 * @staticvar array $errors
	 * @return type
	 */
	public function getErrorString()
	{
		static $errors = [
			UPLOAD_ERR_INI_SIZE   => 'The file "%s" exceeds your upload_max_filesize ini directive.',
			UPLOAD_ERR_FORM_SIZE  => 'The file "%s" exceeds the upload limit defined in your form.',
			UPLOAD_ERR_PARTIAL    => 'The file "%s" was only partially uploaded.',
			UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
			UPLOAD_ERR_CANT_WRITE => 'The file "%s" could not be written on disk.',
			UPLOAD_ERR_NO_TMP_DIR => 'File could not be uploaded: missing temporary directory.',
			UPLOAD_ERR_EXTENSION  => 'File upload was stopped by a PHP extension.',
		];

	    $error = is_null($this->error) ? UPLOAD_ERR_OK : $this->error;

		return isset($errors[$error])
			? sprintf($errors[$error], $this->getName())
			: sprintf('The file "%s" was not uploaded due to an unknown error.', $this->getName());
	}

	//--------------------------------------------------------------------


	/**
	 * Retrieve the filename. This will typically be the filename sent
	 * by the client, and should not be trusted. If the file has been
	 * moved, this will return the final name of the moved file.
	 *
	 * @return string|null The filename sent by the client or null if none
	 *     was provided.
	 */
	public function getName(): string
	{
		return $this->name;
	}

	//--------------------------------------------------------------------

	/**
	 * Gets the temporary filename where the file was uploaded to.
	 *
	 * @return string
	 */
	public function getTempName(): string
	{
		return $this->path;
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
	 * Attempts to determine the file extension based on the trusted
	 * getType() method. If the mime type is unknown, will return null.
	 *
	 * @return string
	 */
	public function getExtension(): string
	{
		return \Config\Mimes::guessExtensionFromType($this->getType());
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the original file extension, based on the file name that
	 * was uploaded. This is NOT a trusted source.
	 * For a trusted version, use guessExtension() instead.
	 *
	 * @return string|null
	 */
	public function getClientExtension(): string
	{
		return pathinfo($this->path, PATHINFO_EXTENSION);
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieve the media type of the file. SHOULD not use information from
	 * the $_FILES array, but should use other methods to more accurately
	 * determine the type of file, like finfo, or mime_content_type().
	 *
	 * @return string|null The media type we determined it to be.
	 */
	public function getType(): string
	{
		if (! is_null($this->mimeType))
		{
			return $this->mimeType;
		}

		if (function_exists('finfo_file'))
		{
			$finfo          = finfo_open(FILEINFO_MIME_TYPE);
			$this->mimeType = finfo_file($finfo, $this->path);
			finfo_close($finfo);
		}
		else
		{
			$this->mimeType = mime_content_type($this->path);
		}

		return $this->mimeType;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the mime type as provided by the client.
	 * This is NOT a trusted value.
	 * For a trusted version, use getMimeType() instead.
	 *
	 * @return string|null The media type sent by the client or null if none
	 *                     was provided.
	 */
	public function getClientType(): string
	{
		return $this->originalMimeType;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns whether the file was uploaded successfully, based on whether
	 * it was uploaded via HTTP and has no errors.
	 *
	 * @return bool
	 */
	public function isValid(): bool
	{
		return is_uploaded_file($this->path) && $this->error === UPLOAD_ERR_OK;
	}

	//--------------------------------------------------------------------

}
