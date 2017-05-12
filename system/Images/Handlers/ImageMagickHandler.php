<?php namespace CodeIgniter\Images\Handlers;

use CodeIgniter\Images\Exceptions\ImageException;

/**
 * Class ImageMagickHandler
 *
 * To make this library as compatible as possible with the broadest
 * number of installations, we do not use the Imagick extension,
 * but simply use the command line version.
 *
 * @package CodeIgniter\Images\Handlers
 */
class ImageMagickHandler extends BaseHandler
{
	public $version;

	/**
	 * Stores image resource in memory.
	 *
	 * @var
	 */
	protected $resource;

	//--------------------------------------------------------------------

	/**
	 * Handles the rotation of an image resource.
	 * Doesn't save the image, but replaces the current resource.
	 *
	 * @param int $angle
	 *
	 * @return bool
	 */
	protected function _rotate(int $angle)
	{

	}

	//--------------------------------------------------------------------

	/**
	 * Flips an image along it's vertical or horizontal axis.
	 *
	 * @param string $direction
	 *
	 * @return $this
	 */
	public function _flip(string $direction)
	{

	}

	//--------------------------------------------------------------------

	/**
	 * Get GD version
	 *
	 * @return    mixed
	 */
	public function getVersion()
	{
		$result = $this->process('-version');

		// The first line has the version in it...
		preg_match('/(ImageMagick\s[\S]+)/', $result[0], $matches);

		return str_replace('ImageMagick ', '', $matches[0]);
	}

	//--------------------------------------------------------------------

	/**
	 * Handles all of the grunt work of resizing, etc.
	 *
	 * @param string $action
	 *
	 * @return $this|bool
	 */
	protected function process(string $action)
	{
		// Do we have a vaild library path?
		if (empty($this->config->libraryPath))
		{
			throw new ImageException(lang('images.libPathInvalid'));
		}

		if ( ! preg_match('/convert$/i', $this->config->libraryPath))
		{
			$this->config->libraryPath = rtrim($this->config->libraryPath, '/').'/convert';
		}

		$cmd = $this->config->libraryPath.' '.$action;

		$retval = 1;
		// exec() might be disabled
		if (function_usable('exec'))
		{
			@exec($cmd, $output, $retval);
		}

		// Did it work?
		if ($retval > 0)
		{
			throw new ImageException(lang('imageProcessFailed'));
		}

		return $output;
	}

	//--------------------------------------------------------------------

	/**
	 * Saves any changes that have been made to file. If no new filename is
	 * provided, the existing image is overwritten, otherwise a copy of the
	 * file is made at $target.
	 *
	 * Example:
	 *    $image->resize(100, 200, true)
	 *          ->save();
	 *
	 * @param string|null $target
	 * @param int         $quality
	 *
	 * @return bool
	 */
	public function save(string $target = null, int $quality = 90)
	{

	}

	//--------------------------------------------------------------------

	/**
	 * Create Image Resource
	 *
	 * This simply creates an image resource handle
	 * based on the type of image being processed
	 *
	 * @param    string
	 * @param    string
	 *
	 * @return    resource|bool
	 */
	protected function createImage($path = '', $imageType = '')
	{

	}

	//--------------------------------------------------------------------

	/**
	 * Handler-specific method for overlaying text on an image.
	 *
	 * @param string $text
	 * @param array  $options
	 * @param bool   $isShadow  Whether we are drawing the dropshadow or actual text
	 */
	protected function textOverlay(string $text, array $options = [], bool $isShadow=false)
	{

	}

	//--------------------------------------------------------------------
}
