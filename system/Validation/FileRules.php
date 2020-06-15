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
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Validation;

use CodeIgniter\HTTP\RequestInterface;
use Config\Services;

/**
 * File validation rules
 */
class FileRules
{

	/**
	 * Request instance. So we can get access to the files.
	 *
	 * @var \CodeIgniter\HTTP\RequestInterface
	 */
	protected $request;

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 *
	 * @param RequestInterface $request
	 */
	public function __construct(RequestInterface $request = null)
	{
		if (is_null($request))
		{
			$request = Services::request();
		}

		$this->request = $request;
	}

	//--------------------------------------------------------------------

	/**
	 * Verifies that $name is the name of a valid uploaded file.
	 *
	 * @param string $blank
	 * @param string $name
	 *
	 * @return boolean
	 */
	public function uploaded(string $blank = null, string $name): bool
	{
		if (! ($files = $this->request->getFileMultiple($name)))
		{
			$files = [$this->request->getFile($name)];
		}

		foreach ($files as $file)
		{
			if (is_null($file))
			{
				return false;
			}

			if (ENVIRONMENT === 'testing')
			{
				if ($file->getError() !== 0)
				{
					return false;
				}
			}
			else
			{
				// Note: cannot unit test this; no way to over-ride ENVIRONMENT?
				// @codeCoverageIgnoreStart
				if (! $file->isValid())
				{
					return false;
				}
				// @codeCoverageIgnoreEnd
			}
		}

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Verifies if the file's size in Kilobytes is no larger than the parameter.
	 *
	 * @param string|null $blank
	 * @param string      $params
	 *
	 * @return boolean
	 */
	public function max_size(string $blank = null, string $params): bool
	{
		// Grab the file name off the top of the $params
		// after we split it.
		$params = explode(',', $params);
		$name   = array_shift($params);

		if (! ($files = $this->request->getFileMultiple($name)))
		{
			$files = [$this->request->getFile($name)];
		}

		foreach ($files as $file)
		{
			if (is_null($file))
			{
				return false;
			}

			if ($file->getError() === UPLOAD_ERR_NO_FILE)
			{
				return true;
			}

			if ($file->getError() === UPLOAD_ERR_INI_SIZE)
			{
				return false;
			}

			if ($file->getSize() / 1024 > $params[0])
			{
				return false;
			}
		}

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Uses the mime config file to determine if a file is considered an "image",
	 * which for our purposes basically means that it's a raster image or svg.
	 *
	 * @param string|null $blank
	 * @param string      $params
	 *
	 * @return boolean
	 */
	public function is_image(string $blank = null, string $params): bool
	{
		// Grab the file name off the top of the $params
		// after we split it.
		$params = explode(',', $params);
		$name   = array_shift($params);

		if (! ($files = $this->request->getFileMultiple($name)))
		{
			$files = [$this->request->getFile($name)];
		}

		foreach ($files as $file)
		{
			if (is_null($file))
			{
				return false;
			}

			if ($file->getError() === UPLOAD_ERR_NO_FILE)
			{
				return true;
			}

			// We know that our mimes list always has the first mime
			// start with `image` even when then are multiple accepted types.
			$type = \Config\Mimes::guessTypeFromExtension($file->getExtension());

			if (mb_strpos($type, 'image') !== 0)
			{
				return false;
			}
		}

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Checks to see if an uploaded file's mime type matches one in the parameter.
	 *
	 * @param string|null $blank
	 * @param string      $params
	 *
	 * @return boolean
	 */
	public function mime_in(string $blank = null, string $params): bool
	{
		// Grab the file name off the top of the $params
		// after we split it.
		$params = explode(',', $params);
		$name   = array_shift($params);

		if (! ($files = $this->request->getFileMultiple($name)))
		{
			$files = [$this->request->getFile($name)];
		}

		foreach ($files as $file)
		{
			if (is_null($file))
			{
				return false;
			}

			if ($file->getError() === UPLOAD_ERR_NO_FILE)
			{
				return true;
			}

			if (! in_array($file->getMimeType(), $params))
			{
				return false;
			}
		}

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Checks to see if an uploaded file's extension matches one in the parameter.
	 *
	 * @param string|null $blank
	 * @param string      $params
	 *
	 * @return boolean
	 */
	public function ext_in(string $blank = null, string $params): bool
	{
		// Grab the file name off the top of the $params
		// after we split it.
		$params = explode(',', $params);
		$name   = array_shift($params);

		if (! ($files = $this->request->getFileMultiple($name)))
		{
			$files = [$this->request->getFile($name)];
		}

		foreach ($files as $file)
		{
			if (is_null($file))
			{
				return false;
			}

			if ($file->getError() === UPLOAD_ERR_NO_FILE)
			{
				return true;
			}

			if (! in_array($file->getExtension(), $params))
			{
				return false;
			}
		}

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Checks an uploaded file to verify that the dimensions are within
	 * a specified allowable dimension.
	 *
	 * @param string|null $blank
	 * @param string      $params
	 *
	 * @return boolean
	 */
	public function max_dims(string $blank = null, string $params): bool
	{
		// Grab the file name off the top of the $params
		// after we split it.
		$params = explode(',', $params);
		$name   = array_shift($params);

		if (! ($files = $this->request->getFileMultiple($name)))
		{
			$files = [$this->request->getFile($name)];
		}

		foreach ($files as $file)
		{
			if (is_null($file))
			{
				return false;
			}

			if ($file->getError() === UPLOAD_ERR_NO_FILE)
			{
				return true;
			}

			// Get Parameter sizes
			$allowedWidth  = $params[0] ?? 0;
			$allowedHeight = $params[1] ?? 0;

			// Get uploaded image size
			$info       = getimagesize($file->getTempName());
			$fileWidth  = $info[0];
			$fileHeight = $info[1];

			if ($fileWidth > $allowedWidth || $fileHeight > $allowedHeight)
			{
				return false;
			}
		}

		return true;
	}

	//--------------------------------------------------------------------
}
