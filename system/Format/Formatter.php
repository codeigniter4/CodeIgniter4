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
 
 namespace CodeIgniter\Format;

use BadMethodCallException;
use CodeIgniter\Config\BaseConfig;
use InvalidArgumentException;

class Formatter extends BaseConfig
{
	/**
	* List of supported mime types that your application can automatically format
	* the response when perform content negotiation with the request.
	*
	* @var array
	*/
	public $responseFormats = [];

	/**
	* Lists of classes to use to format responses with of a particular type
	* for each mime type.
	*
	* @var array
	*/
	public $formatters = [];

	//--------------------------------------------------------------------

	/**
	 * A Factory method to return the appropriate formatter for the given mime type.
	 *
	 * @param string $mime
	 *
	 * @throws \InvalidArgumentException If formatter not found.
	 * @throws \BadMethodCallException If formatter not valid.
	 *
	 * @return \CodeIgniter\Format\FormatterInterface
	 */
	public function get(string $mime): FormatterInterface
	{
		if (! array_key_exists($mime, $this->formatters))
		{
			throw new InvalidArgumentException("No Formatter defined for mime type: {$mime}");
		}

		if (! class_exists($class = $this->formatters[$mime]))
		{
			throw new BadMethodCallException("{$class} is not a valid Formatter.");
		}

		return new $class();
	}
}
