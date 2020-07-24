<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019-2020 CodeIgniter Foundation
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
 * @copyright  2019-2020 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT    MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Images\Handlers;

use CodeIgniter\Images\Exceptions\ImageException;
use CodeIgniter\Images\Image;
use CodeIgniter\Images\ImageHandlerInterface;
use Config\Images;

/**
 * Base image handling implementation
 */
abstract class BaseHandler implements ImageHandlerInterface
{

	/**
	 * Configuration settings.
	 *
	 * @var \Config\Images
	 */
	protected $config;

	/**
	 * The image/file instance
	 *
	 * @var \CodeIgniter\Images\Image
	 */
	protected $image;

	/**
	 * Whether the image file has been confirmed.
	 *
	 * @var boolean
	 */
	protected $verified = false;

	/**
	 * Image width.
	 *
	 * @var integer
	 */
	protected $width = 0;

	/**
	 * Image height.
	 *
	 * @var integer
	 */
	protected $height = 0;

	/**
	 * File permission mask.
	 *
	 * @var integer
	 */
	protected $filePermissions = 0644;

	/**
	 * X-axis.
	 *
	 * @var integer
	 */
	protected $xAxis = 0;

	/**
	 * Y-axis.
	 *
	 * @var integer
	 */
	protected $yAxis = 0;

	/**
	 * Master dimensioning.
	 *
	 * @var string
	 */
	protected $masterDim = 'auto';

	/**
	 * Default options for text watermarking.
	 *
	 * @var array
	 */
	protected $textDefaults = [
		'fontPath'     => null,
		'fontSize'     => 16,
		'color'        => 'ffffff',
		'opacity'      => 1.0,
		'vAlign'       => 'bottom',
		'hAlign'       => 'center',
		'vOffset'      => 0,
		'hOffset'      => 0,
		'padding'      => 0,
		'withShadow'   => false,
		'shadowColor'  => '000000',
		'shadowOffset' => 3,
	];

	/**
	 * Temporary image used by the different engines.
	 *
	 * @var resource
	 */
	protected $resource;

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 *
	 * @param \Config\Images|null $config
	 */
	public function __construct($config = null)
	{
		$this->config = $config ?? new Images();
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
		// Clear out the old resource so that
		// it doesn't try to use a previous image
		$this->resource = null;
		$this->verified = false;

		$this->image = new Image($path, true);

		$this->image->getProperties(false);
		$this->width  = $this->image->origWidth;
		$this->height = $this->image->origHeight;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Make the image resource object if needed
	 */
	protected abstract function ensureResource();

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

	/**
	 * Verifies that a file has been supplied and it is an image.
	 *
	 * @return Image  The image instance
	 * @throws ImageException
	 */
	protected function image(): Image
	{
		if ($this->verified)
		{
			return $this->image;
		}

		// Verify withFile has been called
		if (empty($this->image))
		{
			throw ImageException::forMissingImage();
		}

		// Verify the loaded image is an Image instance
		if (! $this->image instanceof Image)
		{
			throw ImageException::forInvalidPath();
		}

		// File::__construct has verified the file exists - make sure it is an image
		if (! is_int($this->image->imageType))
		{
			throw ImageException::forFileNotSupported();
		}

		// Note that the image has been verified
		$this->verified = true;

		return $this->image;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the temporary image used during the image processing.
	 * Good for extending the system or doing things this library
	 * is not intended to do.
	 *
	 * @return resource
	 */
	public function getResource()
	{
		$this->ensureResource();
		return $this->resource;
	}

	//--------------------------------------------------------------------

	/**
	 * Load the temporary image used during the image processing.
	 * Some functions e.g. save() will only copy and not compress
	 * your image otherwise.
	 *
	 * @return $this
	 */
	public function withResource()
	{
		$this->ensureResource();
		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Resize the image
	 *
	 * @param integer $width
	 * @param integer $height
	 * @param boolean $maintainRatio If true, will get the closest match possible while keeping aspect ratio true.
	 * @param string  $masterDim
	 *
	 * @return BaseHandler
	 */
	public function resize(int $width, int $height, bool $maintainRatio = false, string $masterDim = 'auto')
	{
		// If the target width/height match the source, then we have nothing to do here.
		if ($this->image()->origWidth === $width && $this->image()->origHeight === $height)
		{
			return $this;
		}

		$this->width  = $width;
		$this->height = $height;

		if ($maintainRatio)
		{
			$this->masterDim = $masterDim;
			$this->reproportion();
		}

		return $this->_resize($maintainRatio);
	}

	//--------------------------------------------------------------------

	/**
	 * Crops the image to the desired height and width. If one of the height/width values
	 * is not provided, that value will be set the appropriate value based on offsets and
	 * image dimensions.
	 *
	 * @param integer|null $width
	 * @param integer|null $height
	 * @param integer|null $x             X-axis coord to start cropping from the left of image
	 * @param integer|null $y             Y-axis coord to start cropping from the top of image
	 * @param boolean      $maintainRatio
	 * @param string       $masterDim
	 *
	 * @return $this
	 */
	public function crop(int $width = null, int $height = null, int $x = null, int $y = null, bool $maintainRatio = false, string $masterDim = 'auto')
	{
		$this->width  = $width;
		$this->height = $height;
		$this->xAxis  = $x;
		$this->yAxis  = $y;

		if ($maintainRatio)
		{
			$this->masterDim = $masterDim;
			$this->reproportion();
		}

		$result = $this->_crop();

		$this->xAxis = null;
		$this->yAxis = null;

		return $result;
	}

	//--------------------------------------------------------------------

	/**
	 * Changes the stored image type to indicate the new file format to use when saving.
	 * Does not touch the actual resource.
	 *
	 * @param integer|null $imageType A PHP imageType constant, e.g. https://www.php.net/manual/en/function.image-type-to-mime-type.php
	 *
	 * @return $this
	 */
	public function convert(int $imageType)
	{
		$this->image()->imageType = $imageType;
		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Rotates the image on the current canvas.
	 *
	 * @param float $angle
	 *
	 * @return $this
	 */
	public function rotate(float $angle)
	{
		// Allowed rotation values
		$degs = [
			90,
			180,
			270,
		];

		if ($angle === '' || ! in_array($angle, $degs))
		{
			throw ImageException::forMissingAngle();
		}

		// cast angle as an int, for our use
		$angle = (int) $angle;

		// Reassign the width and height
		if ($angle === 90 || $angle === 270)
		{
			$temp         = $this->height;
			$this->width  = $this->height;
			$this->height = $temp;
		}

		// Call the Handler-specific version.
		$this->_rotate($angle);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Flattens transparencies, default white background
	 *
	 * @param integer $red
	 * @param integer $green
	 * @param integer $blue
	 *
	 * @return $this
	 */
	public function flatten(int $red = 255, int $green = 255, int $blue = 255)
	{
		$this->width  = $this->image()->origWidth;
		$this->height = $this->image()->origHeight;

		return $this->_flatten();
	}

	//--------------------------------------------------------------------

	/**
	 * Handler-specific method to flattening an image's transparencies.
	 *
	 * @param integer $red
	 * @param integer $green
	 * @param integer $blue
	 *
	 * @return   $this
	 * @internal
	 */
	protected abstract function _flatten(int $red = 255, int $green = 255, int $blue = 255);

	//--------------------------------------------------------------------

	/**
	 * Handler-specific method to handle rotating an image in 90 degree increments.
	 *
	 * @param integer $angle
	 *
	 * @return mixed
	 */
	protected abstract function _rotate(int $angle);

	//--------------------------------------------------------------------

	/**
	 * Flips an image either horizontally or vertically.
	 *
	 * @param string $dir Either 'vertical' or 'horizontal'
	 *
	 * @return $this
	 */
	public function flip(string $dir = 'vertical')
	{
		$dir = strtolower($dir);

		if ($dir !== 'vertical' && $dir !== 'horizontal')
		{
			throw ImageException::forInvalidDirection($dir);
		}

		return $this->_flip($dir);
	}

	//--------------------------------------------------------------------

	/**
	 * Handler-specific method to handle flipping an image along its
	 * horizontal or vertical axis.
	 *
	 * @param string $direction
	 *
	 * @return $this
	 */
	protected abstract function _flip(string $direction);

	//--------------------------------------------------------------------

	/**
	 * Overlays a string of text over the image.
	 *
	 * Valid options:
	 *
	 *  - color         Text Color (hex number)
	 *  - shadowColor   Color of the shadow (hex number)
	 *  - hAlign        Horizontal alignment: left, center, right
	 *  - vAlign        Vertical alignment: top, middle, bottom
	 *  - hOffset
	 *  - vOffset
	 *  - fontPath
	 *  - fontSize
	 *  - shadowOffset
	 *
	 * @param string $text
	 * @param array  $options
	 *
	 * @return $this
	 */
	public function text(string $text, array $options = [])
	{
		$options                = array_merge($this->textDefaults, $options);
		$options['color']       = trim($options['color'], '# ');
		$options['shadowColor'] = trim($options['shadowColor'], '# ');

		$this->_text($text, $options);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Handler-specific method for overlaying text on an image.
	 *
	 * @param string $text
	 * @param array  $options
	 */
	protected abstract function _text(string $text, array $options = []);

	//--------------------------------------------------------------------

	/**
	 * Reads the EXIF information from the image and modifies the orientation
	 * so that displays correctly in the browser. This is especially an issue
	 * with images taken by smartphones who always store the image up-right,
	 * but set the orientation flag to display it correctly.
	 *
	 * @param boolean $silent If true, will ignore exceptions when PHP doesn't support EXIF.
	 *
	 * @return $this
	 */
	public function reorient(bool $silent = false)
	{
		$orientation = $this->getEXIF('Orientation', $silent);

		switch ($orientation)
		{
			case 2:
				return $this->flip('horizontal');
			case 3:
				return $this->rotate(180);
			case 4:
				return $this->rotate(180)
								->flip('horizontal');
			case 5:
				return $this->rotate(270)
								->flip('horizontal');
			case 6:
				return $this->rotate(270);
			case 7:
				return $this->rotate(90)
								->flip('horizontal');
			case 8:
				return $this->rotate(90);
			default:
				return $this;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieve the EXIF information from the image, if possible. Returns
	 * an array of the information, or null if nothing can be found.
	 *
	 * EXIF data is only supported fr JPEG & TIFF formats.
	 *
	 * @param string|null $key    If specified, will only return this piece of EXIF data.
	 * @param boolean     $silent If true, will not throw our own exceptions.
	 *
	 * @return mixed
	 */
	public function getEXIF(string $key = null, bool $silent = false)
	{
		if (! function_exists('exif_read_data'))
		{
			if ($silent)
			{
				return null;
			}
		}

		$exif = null; // default
		switch ($this->image()->imageType)
		{
			case IMAGETYPE_JPEG:
			case IMAGETYPE_TIFF_II:
				$exif = exif_read_data($this->image()->getPathname());
				if (! is_null($key) && is_array($exif))
				{
					$exif = $exif[$key] ?? false;
				}
		}

		return $exif;
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
	 * @param integer $width
	 * @param integer $height
	 * @param string  $position
	 *
	 * @return $this
	 */
	public function fit(int $width, int $height = null, string $position = 'center')
	{
		$origWidth  = $this->image()->origWidth;
		$origHeight = $this->image()->origHeight;

		list($cropWidth, $cropHeight) = $this->calcAspectRatio($width, $height, $origWidth, $origHeight);

		if (is_null($height))
		{
			$height = ceil(($width / $cropWidth) * $cropHeight);
		}

		list($x, $y) = $this->calcCropCoords($cropWidth, $cropHeight, $origWidth, $origHeight, $position);

		return $this->crop($cropWidth, $cropHeight, $x, $y)
						->resize($width, $height);
	}

	//--------------------------------------------------------------------

	/**
	 * Calculate image aspect ratio.
	 *
	 * @param integer|float      $width
	 * @param integer|float|null $height
	 * @param integer|float      $origWidth
	 * @param integer|float      $origHeight
	 *
	 * @return array
	 */
	protected function calcAspectRatio($width, $height = null, $origWidth, $origHeight): array
	{
		// If $height is null, then we have it easy.
		// Calc based on full image size and be done.
		if (is_null($height))
		{
			$height = ($width / $origWidth) * $origHeight;

			return [
				$width,
				(int) $height,
			];
		}

		$xRatio = $width / $origWidth;
		$yRatio = $height / $origHeight;

		if ($xRatio > $yRatio)
		{
			return [
				$origWidth,
				(int) ($origWidth * $height / $width),
			];
		}

		return [
			(int) ($origHeight * $width / $height),
			$origHeight,
		];
	}

	//--------------------------------------------------------------------

	/**
	 * Based on the position, will determine the correct x/y coords to
	 * crop the desired portion from the image.
	 *
	 * @param integer|float $width
	 * @param integer|float $height
	 * @param integer|float $origWidth
	 * @param integer|float $origHeight
	 * @param string        $position
	 *
	 * @return array
	 */
	protected function calcCropCoords($width, $height, $origWidth, $origHeight, $position): array
	{
		$position = strtolower($position);
		$x        = $y = 0;

		switch ($position)
		{
			case 'top-left':
				$x = 0;
				$y = 0;
				break;
			case 'top':
				$x = floor(($origWidth - $width) / 2);
				$y = 0;
				break;
			case 'top-right':
				$x = $origWidth - $width;
				$y = 0;
				break;
			case 'left':
				$x = 0;
				$y = floor(($origHeight - $height) / 2);
				break;
			case 'center':
				$x = floor(($origWidth - $width) / 2);
				$y = floor(($origHeight - $height) / 2);
				break;
			case 'right':
				$x = ($origWidth - $width);
				$y = floor(($origHeight - $height) / 2);
				break;
			case 'bottom-left':
				$x = 0;
				$y = $origHeight - $height;
				break;
			case 'bottom':
				$x = floor(($origWidth - $width) / 2);
				$y = $origHeight - $height;
				break;
			case 'bottom-right':
				$x = ($origWidth - $width);
				$y = $origHeight - $height;
				break;
		}

		return [
			$x,
			$y,
		];
	}

	//--------------------------------------------------------------------

	/**
	 * Get the version of the image library in use.
	 *
	 * @return string
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
	 * @param string|null $target
	 * @param integer     $quality
	 *
	 * @return boolean
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
	 *
	 * @return mixed
	 */
	public function __call(string $name, array $args = [])
	{
		if (method_exists($this->image(), $name))
		{
			return $this->image()->$name(...$args);
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
	 * @return void
	 */
	protected function reproportion()
	{
		if (($this->width === 0 && $this->height === 0) ||
				$this->image()->origWidth === 0 ||
				$this->image()->origHeight === 0 ||
				( ! ctype_digit((string) $this->width) && ! ctype_digit((string) $this->height)) ||
				! ctype_digit((string) $this->image()->origWidth) ||
				! ctype_digit((string) $this->image()->origHeight)
		)
		{
			return;
		}

		// Sanitize
		$this->width  = (int) $this->width;
		$this->height = (int) $this->height;

		if ($this->masterDim !== 'width' && $this->masterDim !== 'height')
		{
			if ($this->width > 0 && $this->height > 0)
			{
				$this->masterDim = ((($this->image()->origHeight / $this->image()->origWidth) - ($this->height / $this->width)) < 0) ? 'width' : 'height';
			}
			else
			{
				$this->masterDim = ($this->height === 0) ? 'width' : 'height';
			}
		}
		elseif (($this->masterDim === 'width' && $this->width === 0) || ($this->masterDim === 'height' && $this->height === 0)
		)
		{
			return;
		}

		if ($this->masterDim === 'width')
		{
			$this->height = (int) ceil($this->width * $this->image()->origHeight / $this->image()->origWidth);
		}
		else
		{
			$this->width = (int) ceil($this->image()->origWidth * $this->height / $this->image()->origHeight);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Return image width.
	 *
	 * accessor for testing; not part of interface
	 *
	 * @return integer
	 */
	public function getWidth()
	{
		return ($this->resource !== null) ? $this->_getWidth() : $this->width;
	}

	/**
	 * Return image height.
	 *
	 * accessor for testing; not part of interface
	 *
	 * @return integer
	 */
	public function getHeight()
	{
		return ($this->resource !== null) ? $this->_getHeight() : $this->height;
	}

}
