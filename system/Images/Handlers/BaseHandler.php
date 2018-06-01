<?php namespace CodeIgniter\Images\Handlers;

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
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2014-2018 British Columbia Institute of Technology (https://bcit.ca/)
 * @license    https://opensource.org/licenses/MIT    MIT License
 * @link       https://codeigniter.com
 * @since      Version 3.0.0
 * @filesource
 */
use CodeIgniter\Images\Exceptions\ImageException;
use CodeIgniter\Images\Image;
use CodeIgniter\Images\ImageHandlerInterface;

abstract class BaseHandler implements ImageHandlerInterface
{

	/**
	 * @var \Config\Images
	 */
	protected $config;

	/**
	 * The image/file instance
	 * d
	 * @var \CodeIgniter\Images\Image
	 */
	protected $image = null;
	protected $width = 0;
	protected $height = 0;
	protected $filePermissions = 0644;
	protected $xAxis = 0;
	protected $yAxis = 0;
	protected $masterDim = 'auto';

	/**
	 * Default options for text watermarking.
	 *
	 * @var array
	 */
	protected $textDefaults = [
		'fontPath'		 => null,
		'fontSize'		 => 16,
		'color'			 => 'ffffff',
		'opacity'		 => 1.0,
		'vAlign'		 => 'bottom',
		'hAlign'		 => 'center',
		'vOffset'		 => 0,
		'hOffset'		 => 0,
		'padding'		 => 0,
		'withShadow'	 => false,
		'shadowColor'	 => '000000',
		'shadowOffset'	 => 3,
	];

	/**
	 * Temporary image used by the different engines.
	 *
	 * @var Resource
	 */
	protected $resource;

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
		// Clear out the old resource so that
		// it doesn't try to use a previous image
		$this->resource = null;

		$this->image = new Image($path, true);

		$this->image->getProperties(false);
		$this->width = $this->image->origWidth;
		$this->height = $this->image->origHeight;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Make the image resource object if needed
	 */
	protected function ensureResource()
	{
		if ($this->resource == null)
		{
			$path = $this->image->getPathname();
			// if valid image type, make corresponding image resource
			switch ($this->image->imageType)
			{
				case IMAGETYPE_GIF:
					$this->resource = imagecreatefromgif($path);
					break;
				case IMAGETYPE_JPEG:
					$this->resource = imagecreatefromjpeg($path);
					break;
				case IMAGETYPE_PNG:
					$this->resource = imagecreatefrompng($path);
					break;
			}
		}
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
	 * Returns the temporary image used during the image processing.
	 * Good for extending the system or doing things this library
	 * is not intended to do.
	 *
	 * @return Resource
	 */
	public function getResource()
	{
		$this->ensureResource();
		return $this->resource;
	}

	//--------------------------------------------------------------------

	/**
	 * Resize the image
	 *
	 * @param int  $width
	 * @param int  $height
	 * @param bool $maintainRatio If true, will get the closest match possible while keeping aspect ratio true.
	 * @param string $masterDim
	 *
	 * @return BaseHandler
	 */
	public function resize(int $width, int $height, bool $maintainRatio = false, string $masterDim = 'auto')
	{
		// If the target width/height match the source, then we have nothing to do here.
		if ($this->image->origWidth === $width && $this->image->origHeight === $height)
		{
			return $this;
		}

		$this->width = $width;
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

		$result = $this->_crop();

		$this->xAxis = null;
		$this->yAxis = null;

		return $result;
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
		// Allowed rotation values
		$degs = [90, 180, 270];

		if ($angle === '' || ! in_array($angle, $degs))
		{
			throw ImageException::forMissingAngle();
		}

		// cast angle as an int, for our use
		$angle = (int) $angle;

		// Reassign the width and height
		if ($angle === 90 || $angle === 270)
		{
			$temp = $this->height;
			$this->width = $this->height;
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
	 * @param int $red
	 * @param int $green
	 * @param int $blue
	 *
	 * @return mixed
	 */
	public function flatten(int $red = 255, int $green = 255, int $blue = 255)
	{
		$this->width = $this->image->origWidth;
		$this->height = $this->image->origHeight;

		return $this->_flatten();
	}

	//--------------------------------------------------------------------

	/**
	 * Handler-specific method to flattening an image's transparencies.
	 *
	 * @param int $red
	 * @param int $green
	 * @param int $blue
	 *
	 * @return mixed
	 * @internal param int $angle
	 *
	 */
	protected abstract function _flatten(int $red = 255, int $green = 255, int $blue = 255);

	//--------------------------------------------------------------------

	/**
	 * Handler-specific method to handle rotating an image in 90 degree increments.
	 *
	 * @param int $angle
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
	 * @return mixed
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
		$options = array_merge($this->textDefaults, $options);
		$options['color'] = trim($options['color'], '# ');
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
	 * @param bool $silent If true, will ignore exceptions when PHP doesn't support EXIF.
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
				break;
			case 3:
				return $this->rotate(180);
				break;
			case 4:
				return $this->rotate(180)
								->flip('horizontal');
				break;
			case 5:
				return $this->rotate(270)
								->flip('horizontal');
				break;
			case 6:
				return $this->rotate(270);
				break;
			case 7:
				return $this->rotate(90)
								->flip('horizontal');
				break;
			case 8:
				return $this->rotate(90);
				break;
			default:
				return $this;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieve the EXIF information from the image, if possible. Returns
	 * an array of the information, or null if nothing can be found.
	 *
	 * @param string|null $key    If specified, will only return this piece of EXIF data.
	 *
	 * @param bool        $silent If true, will not throw our own exceptions.
	 *
	 * @return mixed
	 */
	public function getEXIF(string $key = null, bool $silent = false)
	{
		if ( ! function_exists('exif_read_data'))
		{
			if ($silent)
			{
				return null;
			}
		}

		$exif = exif_read_data($this->image->getPathname());
		if ( ! is_null($key) && is_array($exif))
		{
			$exif = array_key_exists($key, $exif) ? $exif[$key] : false;
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
	 * @param int    $width
	 * @param int    $height
	 * @param string $position
	 *
	 * @return bool
	 */
	public function fit(int $width, int $height = null, string $position = 'center')
	{
		$origWidth = $this->image->origWidth;
		$origHeight = $this->image->origHeight;

		list($cropWidth, $cropHeight) = $this->calcAspectRatio($width, $height, $origWidth, $origHeight);

		if (is_null($height))
		{
			$height = ceil(($width / $cropWidth) * $cropHeight);
		}

		list($x, $y) = $this->calcCropCoords($width, $height, $origWidth, $origHeight, $position);

		return $this->crop($cropWidth, $cropHeight, $x, $y)
						->resize($width, $height);
	}

	//--------------------------------------------------------------------

	/**
	 *
	 *
	 * @param      $width
	 * @param null $height
	 * @param      $origWidth
	 * @param      $origHeight
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

			return [$width, (int) $height];
		}

		$xRatio = $width / $origWidth;
		$yRatio = $height / $origHeight;

		if ($xRatio > $yRatio)
		{
			return [
				(int) ($origWidth * $yRatio),
				(int) ($origHeight * $yRatio),
			];
		}

		return [
			(int) ($origWidth * $xRatio),
			(int) ($origHeight * $xRatio),
		];
	}

	//--------------------------------------------------------------------

	/**
	 * Based on the position, will determine the correct x/y coords to
	 * crop the desired portion from the image.
	 *
	 * @param $width
	 * @param $height
	 * @param $origWidth
	 * @param $origHeight
	 * @param $position
	 *
	 * @return array
	 */
	protected function calcCropCoords($width, $height, $origWidth, $origHeight, $position): array
	{
		$position = strtolower($position);
		$x = $y = 0;

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

		return [$x, $y];
	}

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
		if (($this->width === 0 && $this->height === 0) ||
				$this->image->origWidth === 0 ||
				$this->image->origHeight === 0 ||
				( ! ctype_digit((string) $this->width) && ! ctype_digit((string) $this->height)) ||
				! ctype_digit((string) $this->image->origWidth) ||
				! ctype_digit((string) $this->image->origHeight)
		)
		{
			return;
		}

		// Sanitize
		$this->width = (int) $this->width;
		$this->height = (int) $this->height;

		if ($this->masterDim !== 'width' && $this->masterDim !== 'height')
		{
			if ($this->width > 0 && $this->height > 0)
			{
				$this->masterDim = ((($this->image->origHeight / $this->image->origWidth) - ($this->height / $this->width)) < 0) ? 'width' : 'height';
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
			$this->height = (int) ceil($this->width * $this->image->origHeight / $this->image->origWidth);
		}
		else
		{
			$this->width = (int) ceil($this->image->origWidth * $this->height / $this->image->origHeight);
		}
	}

	//--------------------------------------------------------------------
	// accessor for testing; not part of interface
	public function getWidth()
	{
		return ($this->resource != null) ? $this->_getWidth() : $this->width;
	}

	// accessor for testing; not part of interface
	public function getHeight()
	{
		return ($this->resource != null) ? $this->_getHeight() : $this->height;
	}

}
