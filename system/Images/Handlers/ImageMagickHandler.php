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
	 * Handles the actual resizing of the image.
	 */
	public function _resize(bool $maintainRatio = false)
	{
		$source = ! empty($this->resource) ? $this->resource : $this->image->getPathname();
		$destination = $this->getResourcePath();

		$action = $maintainRatio === true
			? ' -resize '.$this->width.'x'.$this->height.' "'.$source.'" "'.$destination.'"'
			: ' -resize '.$this->width.'x'.$this->height.'\! "'.$source.'" "'.$destination.'"';

		$this->process($action);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Crops the image.
	 *
	 * @return bool|\CodeIgniter\Images\Handlers\ImageMagickHandler
	 */
	public function _crop()
	{
		$source = ! empty($this->resource) ? $this->resource : $this->image->getPathname();
		$destination = $this->getResourcePath();

		$action = ' -crop '.$this->width.'x'.$this->height.'+'.$this->xAxis.'+'.$this->yAxis.' "'.$source.'" "'.$destination .'"';

		$this->process($action);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Handles the rotation of an image resource.
	 * Doesn't save the image, but replaces the current resource.
	 *
	 * @param int $angle
	 *
	 * @return $this
	 */
	protected function _rotate(int $angle)
	{
		$angle = '-rotate '.$angle;

		$source = ! empty($this->resource) ? $this->resource : $this->image->getPathname();
		$destination = $this->getResourcePath();

		$action = ' '.$angle.' "'.$source.'" "'.$destination.'"';

		$this->process($action);

		return $this;
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
		$angle = $direction == 'horizontal' ? '-flop' : '-flip';

		$source = ! empty($this->resource) ? $this->resource : $this->image->getPathname();
		$destination = $this->getResourcePath();

		$action = ' '.$angle.' "'.$source.'" "'.$destination.'"';

		$this->process($action);

		return $this;
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
	protected function process(string $action, int $quality = 100)
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

		$cmd = $this->config->libraryPath.' -quality '.$quality.' '.$action;

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
		$target = empty($target)
			? $this->image
			: $target;

		// If no new resource has been created, then we're
		// simply copy the existing one.
		if (empty($this->resource))
		{
			$name = basename($target);
			$path = pathinfo($target, PATHINFO_DIRNAME);

			return $this->image->copy($path, $name);
		}

		// Copy the file through ImageMagick so that it has
		// a chance to convert file format.
		$action = '"'.$this->resource.'" "'.$target.'"';

		$result = $this->process($action, $quality);

		unlink($this->resource);

		return $result;
	}

	//--------------------------------------------------------------------

	/**
	 * Get Image Resource
	 *
	 * This simply creates an image resource handle
	 * based on the type of image being processed.
	 * Since ImageMagick is used on the cli, we need to
	 * ensure we have a temporary file on the server
	 * that we can use.
	 *
	 * To ensure we can use all features, like transparency,
	 * during the process, we'll use a PNG as the temp file type.
	 *
	 * @return    resource|bool
	 */
	protected function getResourcePath()
	{
		if (! is_null($this->resource))
		{
			return $this->resource;
		}

		$this->resource = WRITEPATH.'cache/'.time().'_'.bin2hex(random_bytes(10)).'.png';

		return $this->resource;
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
