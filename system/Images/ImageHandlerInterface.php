<?php namespace CodeIgniter\Images;

interface ImageHandlerInterface
{
	/**
	 * Resize the image
	 *
	 * @param int  $width
	 * @param int  $height
	 * @param bool $maintainRation  If true, will get the closest match possible while keeping aspect ratio true.
	 */
	public function resize(int $width, int $height, bool $maintainRatio = false);

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
	 * @return mixed
	 */
	public function crop(int $width = null, int $height = null, int $x = null, int $y = null);

	//--------------------------------------------------------------------

	/**
	 * Rotates the image on the current canvas.
	 *
	 * @param float $angle
	 *
	 * @return mixed
	 */
	public function rotate(float $angle);

	//--------------------------------------------------------------------

	/**
	 * Reads the EXIF information from the image and modifies the orientation
	 * so that displays correctly in the browser.
	 *
	 * @return $this
	 */
	public function reorient();

	//--------------------------------------------------------------------

	/**
	 * Retrieve the EXIF information from the image, if possible. Returns
	 * an array of the information, or null if nothing can be found.
	 *
	 * @param string|null $key  If specified, will only return this piece of EXIF data.
	 *
	 * @return mixed
	 */
	public function getEXIF(string $key = null);

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
	 * @return bool
	 */
	public function fit(int $width, int $height, string $position);

	//--------------------------------------------------------------------

}
