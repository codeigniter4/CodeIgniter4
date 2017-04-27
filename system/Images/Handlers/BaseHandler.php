<?php namespace CodeIgniter\Images\Handlers;

use CodeIgniter\Images\Image;
use CodeIgniter\Images\ImageHandlerInterface;

require_once BASEPATH.'Images/Exceptions.php';

abstract class BaseHandler implements ImageHandlerInterface
{
	/**
	 * Stores any errors that were encountered.
	 *
	 * @var array
	 */
	protected $errors = [];

	/**
	 * @var null
	 */
	protected $config;

	/**
	 * The image/file instance
	 *
	 * @var \CodeIgniter\Images\Image
	 */
	protected $image;

	protected $width;
	protected $height;
	protected $filePermissions = 0644;
	protected $xAxis           = 0;
	protected $yAxis           = 0;
	protected $masterDim       = 'auto';

	//--------------------------------------------------------------------

	public function __construct($config = null)
	{
		$this->config = $config;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets another image for this handler to work on.
	 * Keeps us from needing to continually instantiate the handler.
	 *
	 * @param string $path
	 *
	 * @return $this
	 */
	public function withFile(string $path)
	{
		$this->image = new Image($path, true);

		$this->image->getProperties();

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the image instance.
	 *
	 * @return \CodeIgniter\Images\Image
	 */
	public function getFile()
	{
		return $this->image;
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
	 * @param bool $maintainRation If true, will get the closest match possible while keeping aspect ratio true.
	 *
	 * @return bool|\CodeIgniter\Images\Handlers\GDHandler
	 */
	public function resize(int $width, int $height, bool $maintainRatio = false, string $masterDim = 'auto')
	{
		// If the target width/height match the source, then we have nothing to do here.
		if ($this->image->origWidth === $width && $this->image->origHeight === $height)
		{
			return true;
		}

		$this->width  = $width;
		$this->height = $height;

		if ($maintainRatio)
		{
			$this->masterDim = $masterDim;
			$this->reproportion();
		}

		return $this->process('resize');
	}

	//--------------------------------------------------------------------

	/**
	 * Crops the image to the desired height and width. If one of the height/width values
	 * is not provided, that value will be set the appropriate value based on offsets and
	 * image dimensions.
	 *
	 * @param int|null $width
	 * @param int|null $height
	 * @param int|null $x X-axis coord to start cropping from the left of image
	 * @param int|null $y Y-axis coord to start cropping from the top of image
	 * @param bool     $maintainRatio
	 * @param string   $masterDim
	 *
	 * @return mixed
	 */
	public function crop(int $width = null, int $height = null, int $x = null, int $y = null, bool $maintainRatio = false, string $masterDim = 'auto')
	{
		$this->width = $width;
		$this->height = $height;
		$this->xAxis = $x;
		$this->yAxis = $y;

		if ($maintainRatio)
		{
			$this->masterDim = $masterDim;
			$this->reproportion();
		}

		return $this->process('crop');
	}

	//--------------------------------------------------------------------

	/**
	 * Rotates the image on the current canvas.
	 *
	 * @param float $angle
	 *
	 * @return mixed
	 */
	public abstract function rotate(float $angle);

	//--------------------------------------------------------------------

	/**
	 * @return mixed
	 */
	public abstract function watermark();

	//--------------------------------------------------------------------

	/**
	 * Reads the EXIF information from the image and modifies the orientation
	 * so that displays correctly in the browser.
	 *
	 * @return bool
	 */
	public abstract function reorient(): bool;

	//--------------------------------------------------------------------

	/**
	 * Retrieve the EXIF information from the image, if possible. Returns
	 * an array of the information, or null if nothing can be found.
	 *
	 * @param string|null $key If specified, will only return this piece of EXIF data.
	 *
	 * @return mixed
	 */
	public abstract function getEXIF(string $key = null);

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
	public abstract function fit(int $width, int $height, string $position): bool;

	//--------------------------------------------------------------------

	/**
	 * Get the version of the image library in use.
	 *
	 * @return    string
	 */
	public abstract function getVersion();

	//--------------------------------------------------------------------

	/**
	 * Saves any changes that have been made to file.
	 *
	 * Example:
	 *    $image->resize(100, 200, true)
	 *          ->save($target);
	 *
	 * @param string $target
	 * @param int    $quality
	 *
	 * @return mixed
	 */
	public abstract function save(string $target = null, int $quality = 90);

	//--------------------------------------------------------------------

	/**
	 * Does the driver-specific processing of the image.
	 *
	 * @param string $action
	 *
	 * @return mixed
	 */
	protected abstract function process(string $action);

	//--------------------------------------------------------------------

	/**
	 * Provide access to the Image class' methods if they don't exist
	 * on the handler itself.
	 *
	 * @param string $name
	 * @param array  $args
	 */
	public function __call(string $name, array $args = [])
	{
		if (method_exists($this->image, $name))
		{
			return $this->image->$name(...$args);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Re-proportion Image Width/Height
	 *
	 * When creating thumbs, the desired width/height
	 * can end up warping the image due to an incorrect
	 * ratio between the full-sized image and the thumb.
	 *
	 * This function lets us re-proportion the width/height
	 * if users choose to maintain the aspect ratio when resizing.
	 *
	 * @return    void
	 */
	protected function reproportion()
	{
		if (($this->width === 0 && $this->height === 0) OR $this->image->origWidth === 0 OR $this->image->origHeight === 0
		    OR (! ctype_digit((string)$this->width) && ! ctype_digit((string)$this->height))
		    OR ! ctype_digit((string)$this->image->origWidth) OR ! ctype_digit((string)$this->image->origHeight)
		)
		{
			return;
		}

		// Sanitize
		$this->width  = (int)$this->width;
		$this->height = (int)$this->height;

		if ($this->masterDim !== 'width' && $this->masterDim !== 'height')
		{
			if ($this->width > 0 && $this->height > 0)
			{
				$this->masterDim = ((($this->image->origHeight / $this->image->origWidth)-($this->height / $this->width)) < 0)
					? 'width' : 'height';
			}
			else
			{
				$this->masterDim = ($this->height === 0) ? 'width' : 'height';
			}
		}
		elseif (($this->masterDim === 'width' && $this->width === 0) OR ($this->masterDim === 'height' && $this->height === 0)
		)
		{
			return;
		}

		if ($this->masterDim === 'width')
		{
			$this->height = (int)ceil($this->width*$this->image->origHeight/$this->image->origWidth);
		}
		else
		{
			$this->width = (int)ceil($this->image->origWidth*$this->height/$this->image->origHeight);
		}
	}

	//--------------------------------------------------------------------

}
