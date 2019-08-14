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
 * Class ImageMagickHandler
 *
 * To make this library as compatible as possible with the broadest
 * number of installations, we do not use the Imagick extension,
 * but simply use the command line version.
 *
 * hmm - the width & height accessors at the end use the imagick extension.
 *
 * FIXME - This needs conversion & unit testing, to use the imagick extension
 *
 * @package CodeIgniter\Images\Handlers
 */
class ImageMagickHandler extends BaseHandler
{

	/**
	 * Stores image resource in memory.
	 *
	 * @var
	 */
	protected $resource;

	//--------------------------------------------------------------------

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
		if (! (extension_loaded('imagick') || class_exists('Imagick')))
		{
			throw ImageException::forMissingExtension('IMAGICK');
		}
		// @codeCoverageIgnoreEnd
	}

	//--------------------------------------------------------------------

	/**
	 * Handles the actual resizing of the image.
	 *
	 * @param boolean $maintainRatio
	 *
	 * @return ImageMagickHandler
	 * @throws \Exception
	 */
	public function _resize(bool $maintainRatio = false)
	{
		$source      = ! empty($this->resource) ? $this->resource : $this->image->getPathname();
		$destination = $this->getResourcePath();

		$escape = '\\';
		if (stripos(PHP_OS, 'WIN') === 0)
		{
			$escape = '';
		}

		$action = $maintainRatio === true ? ' -resize ' . $this->width . 'x' . $this->height . ' "' . $source . '" "' . $destination . '"' : ' -resize ' . $this->width . 'x' . $this->height . "{$escape}! \"" . $source . '" "' . $destination . '"';

		$this->process($action);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Crops the image.
	 *
	 * @return boolean|\CodeIgniter\Images\Handlers\ImageMagickHandler
	 * @throws \Exception
	 */
	public function _crop()
	{
		$source      = ! empty($this->resource) ? $this->resource : $this->image->getPathname();
		$destination = $this->getResourcePath();

		$action = ' -crop ' . $this->width . 'x' . $this->height . '+' . $this->xAxis . '+' . $this->yAxis . ' "' . $source . '" "' . $destination . '"';

		$this->process($action);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Handles the rotation of an image resource.
	 * Doesn't save the image, but replaces the current resource.
	 *
	 * @param integer $angle
	 *
	 * @return $this
	 * @throws \Exception
	 */
	protected function _rotate(int $angle)
	{
		$angle = '-rotate ' . $angle;

		$source      = ! empty($this->resource) ? $this->resource : $this->image->getPathname();
		$destination = $this->getResourcePath();

		$action = ' ' . $angle . ' "' . $source . '" "' . $destination . '"';

		$this->process($action);

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
	 * @throws \Exception
	 */
	public function _flatten(int $red = 255, int $green = 255, int $blue = 255)
	{
		$flatten = "-background RGB({$red},{$green},{$blue}) -flatten";

		$source      = ! empty($this->resource) ? $this->resource : $this->image->getPathname();
		$destination = $this->getResourcePath();

		$action = ' ' . $flatten . ' "' . $source . '" "' . $destination . '"';

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
	 * @throws \Exception
	 */
	public function _flip(string $direction)
	{
		$angle = $direction === 'horizontal' ? '-flop' : '-flip';

		$source      = ! empty($this->resource) ? $this->resource : $this->image->getPathname();
		$destination = $this->getResourcePath();

		$action = ' ' . $angle . ' "' . $source . '" "' . $destination . '"';

		$this->process($action);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Get driver version
	 *
	 * @return string
	 */
	public function getVersion(): string
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
	 * @param string  $action
	 * @param integer $quality
	 *
	 * @return ImageMagickHandler|boolean
	 */
	protected function process(string $action, int $quality = 100)
	{
		// Do we have a vaild library path?
		if (empty($this->config->libraryPath))
		{
			throw ImageException::forInvalidImageLibraryPath($this->config->libraryPath);
		}

		if (! preg_match('/convert$/i', $this->config->libraryPath))
		{
			$this->config->libraryPath = rtrim($this->config->libraryPath, '/') . '/convert';
		}

		$cmd  = $this->config->libraryPath;
		$cmd .= $action === '-version' ? ' ' . $action : ' -quality ' . $quality . ' ' . $action;

		$retval = 1;
		// exec() might be disabled
		if (function_usable('exec'))
		{
			@exec($cmd, $output, $retval);
		}

		// Did it work?
		if ($retval > 0)
		{
			throw ImageException::forImageProcessFailed();
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
	 * @param integer     $quality
	 *
	 * @return boolean
	 */
	public function save(string $target = null, int $quality = 90): bool
	{
		$target = empty($target) ? $this->image : $target;

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
		$action = '"' . $this->resource . '" "' . $target . '"';

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
	 * @return resource|boolean
	 * @throws \Exception
	 */
	protected function getResourcePath()
	{
		if (! is_null($this->resource))
		{
			return $this->resource;
		}

		$this->resource = WRITEPATH . 'cache/' . time() . '_' . bin2hex(random_bytes(10)) . '.png';

		return $this->resource;
	}

	//--------------------------------------------------------------------

	/**
	 * Handler-specific method for overlaying text on an image.
	 *
	 * @param string $text
	 * @param array  $options
	 *
	 * @throws \Exception
	 */
	protected function _text(string $text, array $options = [])
	{
		$cmd = '';

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

		// Font
		if (! empty($options['fontPath']))
		{
			$cmd .= " -font '{$options['fontPath']}'";
		}

		if (isset($options['hAlign']) && isset($options['vAlign']))
		{
			switch ($options['hAlign'])
			{
				case 'left':
					$xAxis   = $options['hOffset'] + $options['padding'];
					$yAxis   = $options['vOffset'] + $options['padding'];
					$gravity = $options['vAlign'] === 'top' ? 'NorthWest' : 'West';
					if ($options['vAlign'] === 'bottom')
					{
						$gravity = 'SouthWest';
						$yAxis   = $options['vOffset'] - $options['padding'];
					}
					break;
				case 'center':
					$xAxis   = $options['hOffset'] + $options['padding'];
					$yAxis   = $options['vOffset'] + $options['padding'];
					$gravity = $options['vAlign'] === 'top' ? 'North' : 'Center';
					if ($options['vAlign'] === 'bottom')
					{
						$yAxis   = $options['vOffset'] - $options['padding'];
						$gravity = 'South';
					}
					break;
				case 'right':
					$xAxis   = $options['hOffset'] - $options['padding'];
					$yAxis   = $options['vOffset'] + $options['padding'];
					$gravity = $options['vAlign'] === 'top' ? 'NorthEast' : 'East';
					if ($options['vAlign'] === 'bottom')
					{
						$gravity = 'SouthEast';
						$yAxis   = $options['vOffset'] - $options['padding'];
					}
					break;
			}

			$xAxis = $xAxis >= 0 ? '+' . $xAxis : $xAxis;
			$yAxis = $yAxis >= 0 ? '+' . $yAxis : $yAxis;

			$cmd .= " -gravity {$gravity} -geometry {$xAxis}{$yAxis}";
		}

		// Color
		if (isset($options['color']))
		{
			list($r, $g, $b) = sscanf("#{$options['color']}", '#%02x%02x%02x');

			$cmd .= " -fill 'rgba({$r},{$g},{$b},{$options['opacity']})'";
		}

		// Font Size - use points....
		if (isset($options['fontSize']))
		{
			$cmd .= " -pointsize {$options['fontSize']}";
		}

		// Text
		$cmd .= " -annotate 0 '{$text}'";

		$source      = ! empty($this->resource) ? $this->resource : $this->image->getPathname();
		$destination = $this->getResourcePath();

		$cmd = " '{$source}' {$cmd} '{$destination}'";

		$this->process($cmd);
	}

	//--------------------------------------------------------------------

	/**
	 * Return the width of an image.
	 *
	 * @return type
	 */
	public function _getWidth()
	{
		return imagesx($this->resource);
	}

	/**
	 * Return the height of an image.
	 *
	 * @return type
	 */
	public function _getHeight()
	{
		return imagesy($this->resource);
	}

}
