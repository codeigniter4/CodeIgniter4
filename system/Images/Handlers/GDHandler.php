<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
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
 * @copyright  2014-2019 British Columbia Institute of Technology (https://bcit.ca/)
 * @license    https://opensource.org/licenses/MIT    MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Images\Handlers;

use CodeIgniter\Images\Exceptions\ImageException;

/**
 * Image handler for GD package
 */
class GDHandler extends BaseHandler
{

	/**
	 * Constructor.
	 *
	 * @param  type $config
	 * @throws type
	 */
	public function __construct($config = null)
	{
		parent::__construct($config);

		// We should never see this, so can't test it
		// @codeCoverageIgnoreStart
		if (! extension_loaded('gd'))
		{
			throw ImageException::forMissingExtension('GD');
		}
		// @codeCoverageIgnoreEnd
	}

	//--------------------------------------------------------------------

	/**
	 * Handles the rotation of an image resource.
	 * Doesn't save the image, but replaces the current resource.
	 *
	 * @param integer $angle
	 *
	 * @return boolean
	 */
	protected function _rotate(int $angle): bool
	{
		// Create the image handle
		$srcImg = $this->createImage();

		// Set the background color
		// This won't work with transparent PNG files so we are
		// going to have to figure out how to determine the color
		// of the alpha channel in a future release.

		$white = imagecolorallocate($srcImg, 255, 255, 255);

		// Rotate it!
		$destImg = imagerotate($srcImg, $angle, $white);

		// Kill the file handles
		imagedestroy($srcImg);

		$this->resource = $destImg;

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Flattens transparencies
	 *
	 * @param integer $red
	 * @param integer $green
	 * @param integer $blue
	 *
	 * @return $this
	 */
	public function _flatten(int $red = 255, int $green = 255, int $blue = 255)
	{
		$srcImg = $this->createImage();

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

		$matte = imagecolorallocate($dest, $red, $green, $blue);

		imagefilledrectangle($dest, 0, 0, $this->width, $this->height, $matte);
		imagecopy($dest, $srcImg, 0, 0, 0, 0, $this->width, $this->height);

		// Kill the file handles
		imagedestroy($srcImg);

		$this->resource = $dest;

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
		$srcImg = $this->createImage();

		$width  = $this->image->origWidth;
		$height = $this->image->origHeight;

		if ($direction === 'horizontal')
		{
			for ($i = 0; $i < $height; $i ++)
			{
				$left  = 0;
				$right = $width - 1;

				while ($left < $right)
				{
					$cl = imagecolorat($srcImg, $left, $i);
					$cr = imagecolorat($srcImg, $right, $i);

					imagesetpixel($srcImg, $left, $i, $cr);
					imagesetpixel($srcImg, $right, $i, $cl);

					$left ++;
					$right --;
				}
			}
		}
		else
		{
			for ($i = 0; $i < $width; $i ++)
			{
				$top    = 0;
				$bottom = $height - 1;

				while ($top < $bottom)
				{
					$ct = imagecolorat($srcImg, $i, $top);
					$cb = imagecolorat($srcImg, $i, $bottom);

					imagesetpixel($srcImg, $i, $top, $cb);
					imagesetpixel($srcImg, $i, $bottom, $ct);

					$top ++;
					$bottom --;
				}
			}
		}

		$this->resource = $srcImg;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Get GD version
	 *
	 * @return mixed
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
	 * Resizes the image.
	 *
	 * @return boolean|\CodeIgniter\Images\Handlers\GDHandler
	 */
	public function _resize()
	{
		return $this->process('resize');
	}

	//--------------------------------------------------------------------

	/**
	 * Crops the image.
	 *
	 * @return boolean|\CodeIgniter\Images\Handlers\GDHandler
	 */
	public function _crop()
	{
		return $this->process('crop');
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

		if ($action === 'crop')
		{
			// Reassign the source width/height if cropping
			$origWidth  = $this->width;
			$origHeight = $this->height;

			// Modify the "original" width/height to the new
			// values so that methods that come after have the
			// correct size to work with.
			$this->image->origHeight = $this->height;
			$this->image->origWidth  = $this->width;
		}

		// Create the image handle
		$src = $this->createImage();

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

		imagedestroy($src);
		$this->resource = $dest;

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
	 * @param integer     $quality
	 *
	 * @return boolean
	 */
	public function save(string $target = null, int $quality = 90): bool
	{
		$target = empty($target) ? $this->image->getPathname() : $target;

		switch ($this->image->imageType)
		{
			case IMAGETYPE_GIF:
				if (! function_exists('imagegif'))
				{
					throw ImageException::forInvalidImageCreate(lang('images.gifNotSupported'));
				}

				if (! @imagegif($this->resource, $target))
				{
					throw ImageException::forSaveFailed();
				}
				break;
			case IMAGETYPE_JPEG:
				if (! function_exists('imagejpeg'))
				{
					throw ImageException::forInvalidImageCreate(lang('images.jpgNotSupported'));
				}

				if (! @imagejpeg($this->resource, $target, $quality))
				{
					throw ImageException::forSaveFailed();
				}
				break;
			case IMAGETYPE_PNG:
				if (! function_exists('imagepng'))
				{
					throw ImageException::forInvalidImageCreate(lang('images.pngNotSupported'));
				}

				if (! @imagepng($this->resource, $target))
				{
					throw ImageException::forSaveFailed();
				}
				break;
			default:
				throw ImageException::forInvalidImageCreate();
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
	 * @param string $path
	 * @param string $imageType
	 *
	 * @return resource|boolean
	 */
	protected function createImage(string $path = '', string $imageType = '')
	{
		if ($this->resource !== null)
		{
			return $this->resource;
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
					throw ImageException::forInvalidImageCreate(lang('images.gifNotSupported'));
				}

				return imagecreatefromgif($path);
			case IMAGETYPE_JPEG:
				if (! function_exists('imagecreatefromjpeg'))
				{
					throw ImageException::forInvalidImageCreate(lang('images.jpgNotSupported'));
				}

				return imagecreatefromjpeg($path);
			case IMAGETYPE_PNG:
				if (! function_exists('imagecreatefrompng'))
				{
					throw ImageException::forInvalidImageCreate(lang('images.pngNotSupported'));
				}

				return imagecreatefrompng($path);
			default:
				throw ImageException::forInvalidImageCreate('Ima');
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Add text overlay to an image.
	 *
	 * @param string $text
	 * @param array  $options
	 *
	 * @return void
	 */
	protected function _text(string $text, array $options = [])
	{
		// Reverse the vertical offset
		// When the image is positioned at the bottom
		// we don't want the vertical offset to push it
		// further down. We want the reverse, so we'll
		// invert the offset. Note: The horizontal
		// offset flips itself automatically

		if ($options['vAlign'] === 'bottom')
		{
			$options['vOffset'] = $options['vOffset'] * -1;
		}

		if ($options['hAlign'] === 'right')
		{
			$options['hOffset'] = $options['hOffset'] * -1;
		}

		// Set font width and height
		// These are calculated differently depending on
		// whether we are using the true type font or not
		if (! empty($options['fontPath']))
		{
			if (function_exists('imagettfbbox'))
			{
				$temp = imagettfbbox($options['fontSize'], 0, $options['fontPath'], $text);
				$temp = $temp[2] - $temp[0];

				$fontwidth = $temp / strlen($text);
			}
			else
			{
				$fontwidth = $options['fontSize'] - ($options['fontSize'] / 4);
			}

			$fontheight = $options['fontSize'];
		}
		else
		{
			$fontwidth  = imagefontwidth($options['fontSize']);
			$fontheight = imagefontheight($options['fontSize']);
		}

		$options['fontheight'] = $fontheight;
		$options['fontwidth']  = $fontwidth;

		// Set base X and Y axis values
		$xAxis = $options['hOffset'] + $options['padding'];
		$yAxis = $options['vOffset'] + $options['padding'];

		// Set vertical alignment
		if ($options['vAlign'] === 'middle')
		{
			// Don't apply padding when you're in the middle of the image.
			$yAxis += ($this->image->origHeight / 2) + ($fontheight / 2) - $options['padding'];
		}
		elseif ($options['vAlign'] === 'bottom')
		{
			$yAxis = ($this->image->origHeight - $fontheight - $options['shadowOffset'] - ($fontheight / 2)) - $yAxis;
		}

		// Set horizontal alignment
		if ($options['hAlign'] === 'right')
		{
			$xAxis += ($this->image->origWidth - ($fontwidth * strlen($text)) - $options['shadowOffset']) - (2 * $options['padding']);
		}
		elseif ($options['hAlign'] === 'center')
		{
			$xAxis += floor(($this->image->origWidth - ($fontwidth * strlen($text))) / 2);
		}

		$options['xAxis'] = $xAxis;
		$options['yAxis'] = $yAxis;

		if ($options['withShadow'])
		{
			// Offset from text
			$options['xShadow'] = $xAxis + $options['shadowOffset'];
			$options['yShadow'] = $yAxis + $options['shadowOffset'];

			$this->textOverlay($text, $options, true);
		}

		$this->textOverlay($text, $options, false);
	}

	//--------------------------------------------------------------------

	/**
	 * Handler-specific method for overlaying text on an image.
	 *
	 * @param string  $text
	 * @param array   $options
	 * @param boolean $isShadow Whether we are drawing the dropshadow or actual text
	 */
	protected function textOverlay(string $text, array $options = [], bool $isShadow = false)
	{
		$src = $this->createImage();

		/* Set RGB values for shadow
		 *
		 * Get the rest of the string and split it into 2-length
		 * hex values:
		 */
		$opacity = ($options['opacity'] * 127);

		// Allow opacity to be applied to the text
		imagealphablending($src, true);

		$color = $isShadow ? $options['shadowColor'] : $options['color'];
		$color = str_split(substr($color, 0, 6), 2);
		$color = imagecolorclosestalpha($src, hexdec($color[0]), hexdec($color[1]), hexdec($color[2]), $opacity);

		$xAxis = $isShadow ? $options['xShadow'] : $options['xAxis'];
		$yAxis = $isShadow ? $options['yShadow'] : $options['yAxis'];

		// Add the shadow to the source image
		if (! empty($options['fontPath']))
		{
			// We have to add fontheight because imagettftext locates the bottom left corner, not top-left corner.
			imagettftext($src, $options['fontSize'], 0, $xAxis, $yAxis + $options['fontheight'], $color, $options['fontPath'], $text);
		}
		else
		{
			imagestring($src, $options['fontSize'], $xAxis, $yAxis, $text, $color);
		}

		$this->resource = $src;
	}

	//--------------------------------------------------------------------

	/**
	 * Return image width.
	 *
	 * @return integer
	 */
	public function _getWidth()
	{
		return imagesx($this->resource);
	}

	/**
	 * Return image height.
	 *
	 * @return integer
	 */
	public function _getHeight()
	{
		return imagesy($this->resource);
	}

}
