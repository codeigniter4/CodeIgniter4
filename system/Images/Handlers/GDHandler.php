<?php namespace CodeIgniter\Images\Handlers;

use CodeIgniter\Images\ImageException;

class GDHandler extends BaseHandler
{
	public $version;

	/**
	 * Stores image resource in memory.
	 *
	 * @var
	 */
	protected $resource;

	public function __construct($config = null)
	{
		parent::__construct($config);

		if (! extension_loaded('gd'))
		{
			throw new ImageException('GD Extension is not loaded.');
		}

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

	}

	//--------------------------------------------------------------------

	/**
	 * @return mixed
	 */
	public function watermark()
	{

	}

	//--------------------------------------------------------------------

	/**
	 * Reads the EXIF information from the image and modifies the orientation
	 * so that displays correctly in the browser.
	 *
	 * @return bool
	 */
	public function reorient(): bool
	{

	}

	//--------------------------------------------------------------------

	/**
	 * Retrieve the EXIF information from the image, if possible. Returns
	 * an array of the information, or null if nothing can be found.
	 *
	 * @param string|null $key If specified, will only return this piece of EXIF data.
	 *
	 * @return mixed
	 */
	public function getEXIF(string $key = null)
	{

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
	 * @return bool
	 */
	public function fit(int $width, int $height, string $position): bool
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
		if (function_exists('gd_info'))
		{
			$gd_version = @gd_info();

			return preg_replace('/\D/', '', $gd_version['GD Version']);
		}

		return false;
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
		$origWidth  = $this->image->origWidth;
		$origHeight = $this->image->origHeight;

		if ($action == 'crop')
		{
			// Reassign the source width/height if cropping
			$origWidth = $this->width;
			$origHeight = $this->height;
		}

		// Create the image handle
		if (! ($src = $this->createImage()))
		{
			return false;
		}

		if (function_exists('imagecreatetruecolor'))
		{
			$create = 'imagecreatetruecolor';
			$copy   = 'imagecopyresampled';
		}
		else
		{
			$create = 'imagecreate';
			$copy   = 'imagecopyresized';
		}

		$dest = $create($this->width, $this->height);

		if ($this->image->imageType === IMAGETYPE_PNG) // png we can actually preserve transparency
		{
			imagealphablending($dest, false);
			imagesavealpha($dest, true);
		}

		$copy($dest, $src, 0, 0, $this->xAxis, $this->yAxis, $this->width, $this->height, $origWidth, $origHeight);

		$this->resource = $dest;
		imagedestroy($src);

		return $this;
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
	public function save(string $target = null, int $quality=90)
	{
		$target = empty($target)
			? $this->image->getPathname()
			: $target;

		switch ($this->image->imageType)
		{
			case IMAGETYPE_GIF:
				if (! function_exists('imagegif'))
				{
					$this->errors[] = lang('images.unsupportedImagecreate');
					$this->errors[] = lang('images.gifNotSupported');

					return false;
				}

				if (! @imagegif($this->resource, $target))
				{
					$this->errors[] = lang('images.saveFailed');

					return false;
				}
				break;
			case IMAGETYPE_JPEG:
				if (! function_exists('imagejpeg'))
				{
					$this->errors[] = lang('images.unsupportedImagecreate');
					$this->errors[] = lang('images.jpgNotSupported');

					return false;
				}

				if (! @imagejpeg($this->resource, $target, $quality))
				{
					$this->errors[] = lang('images.saveFailed');

					return false;
				}
				break;
			case IMAGETYPE_PNG:
				if (! function_exists('imagepng'))
				{
					$this->errors[] = lang('images.unsupportedImagecreate');
					$this->errors[] = lang('images.pngNotSupported');

					return false;
				}

				if (! @imagepng($this->resource, $target))
				{
					$this->errors[] = lang('images.saveFailed');

					return false;
				}
				break;
			default:
				$this->errors[] = lang('images.unsupportedImagecreate');

				return false;
				break;
		}

		imagedestroy($this->resource);

		chmod($target, $this->filePermissions);

		return true;
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
		if ($this->resource !== null)
		{
			return clone($this->resource);
		}

		if ($path === '')
		{
			$path = $this->image->getPathname();
		}

		if ($imageType === '')
		{
			$imageType = $this->image->imageType;
		}

		switch ($imageType)
		{
			case IMAGETYPE_GIF:
				if (! function_exists('imagecreatefromgif'))
				{
					$this->errors[] = lang('images.gifNotSupported');

					return false;
				}

				return imagecreatefromgif($path);
			case IMAGETYPE_JPEG:
				if (! function_exists('imagecreatefromjpeg'))
				{
					$this->errors[] = lang('images.jpgNotSupported');

					return false;
				}

				return imagecreatefromjpeg($path);
			case IMAGETYPE_PNG:
				if (! function_exists('imagecreatefrompng'))
				{
					$this->errors[] = lang('images.pngNotSupported');

					return false;
				}

				return imagecreatefrompng($path);
			default:
				$this->errors[] = lang('images.unsupportedImagecreate');

				return false;
		}
	}

	//--------------------------------------------------------------------

}
