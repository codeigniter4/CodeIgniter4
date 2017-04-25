<?php namespace CodeIgniter\Images;

use CodeIgniter\Files\File;

class Image extends File {

	/**
	 * @var \CodeIgniter\Images\ImageHandlerInterface
	 */
	protected $handler;

	/**
	 * Stores any errors that were encountered.
	 *
	 * @var array
	 */
	protected $errors = [];

	//--------------------------------------------------------------------

	/**
	 * Sets the Image processign handler that should be used.
	 *
	 * @param \CodeIgniter\Images\ImageHandlerInterface $handler
	 *
	 * @return $this
	 */
	public function setHandler(ImageHandlerInterface $handler)
	{
		$this->handler = $handler;

		return $this;
	}

	//--------------------------------------------------------------------

	public function save(): bool
	{

	}

	//--------------------------------------------------------------------

	public function copy(string $target, int $perms=0644)
	{

	}

	//--------------------------------------------------------------------

	/**
	 * Returns a boolean flag whether any errors were encountered.
	 *
	 * @return bool
	 */
	public function hasErrors(): bool
	{
		return ! empty($this->errors);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns all error messages that were encountered during processing.
	 *
	 * @return array
	 */
	public function getErrors(): array
	{
		return $this->errors ?? [];
	}

	//--------------------------------------------------------------------

	/**
	 * Resize the image
	 *
	 * @param int  $width
	 * @param int  $height
	 * @param bool $maintainRatio If true, will get the closest match possible while keeping aspect ratio true.
	 *
	 * @return $this
	 */
	public function resize(int $width, int $height, bool $maintainRatio = false)
	{
		try {
			$this->handler->resize($width, $height, $maintainRatio);
		}
		catch (ImageException $e)
		{
			$this->errors[] = $e->getMessage();
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Crops the image to the desired height and width. If one of the height/width values
	 * is not provided, that value will be set the appropriate value based on offsets and
	 * image dimensions.
	 *
	 * @param int|null $width
	 * @param int|null $height
	 * @param int|null $x       X-axis coord to start cropping from the left of image
	 * @param int|null $y       Y-axis coord to start cropping from the top of image
	 *
	 * @return $this
	 */
	public function crop(int $width = null, int $height = null, int $x = null, int $y = null)
	{
		try {
			$this->handler->crop($width, $height, $x, $y);
		}
		catch (ImageException $e)
		{
			$this->errors[] = $e->getMessage();
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Rotates the image on the current canvas.
	 *
	 * @param float $angle
	 *
	 * @return mixed
	 */
	public function rotate(float $angle)
	{
		try {
			$this->handler->rotate($angle);
		}
		catch (ImageException $e)
		{
			$this->errors[] = $e->getMessage();
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * @return $this
	 */
	public function watermark()
	{

	}

	//--------------------------------------------------------------------

	/**
	 * Reads the EXIF information from the image and modifies the orientation
	 * so that displays correctly in the browser.
	 *
	 * @return $this
	 */
	public function reorient(): bool
	{
		try {
			$this->handler->reorient();
		}
		catch (ImageException $e)
		{
			$this->errors[] = $e->getMessage();
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieve the EXIF information from the image, if possible. Returns
	 * an array of the information, or null if nothing can be found.
	 *
	 * @param string|null $key  If specified, will only return this piece of EXIF data.
	 *
	 * @return mixed
	 */
	public function getEXIF(string $key = null)
	{
		try {
			$this->handler->getEXIF($key);
		}
		catch (ImageException $e)
		{
			$this->errors[] = $e->getMessage();
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Combine cropping and resizing into a single command.
	 *
	 * Supported positions:
	 *  - top-left
	 *  - top
	 *  - top-right
	 *  - left
	 *  - center
	 *  - right
	 *  - bottom-left
	 *  - bottom
	 *  - bottom-right
	 *
	 * @param int    $width
	 * @param int    $height
	 * @param string $position
	 *
	 * @return $this
	 */
	public function fit(int $width, int $height, string $position)
	{
		try {
			$this->handler->fit($width, $height, $position);
		}
		catch (ImageException $e)
		{
			$this->errors[] = $e->getMessage();
		}

		return $this;
	}

	//--------------------------------------------------------------------

}
