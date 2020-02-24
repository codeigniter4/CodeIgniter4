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

namespace CodeIgniter\Log\Handlers;

/**
 * Base class for logging
 */
abstract class BaseHandler implements HandlerInterface
{

	/**
	 * Handles
	 *
	 * @var array
	 */
	protected $handles;

	/**
	 * Date format for logging
	 *
	 * @var string
	 */
	protected $dateFormat = 'Y-m-d H:i:s';

	//--------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @param array $config
	 */
	public function __construct(array $config)
	{
		$this->handles = $config['handles'] ?? [];
	}

	//--------------------------------------------------------------------

	/**
	 * Checks whether the Handler will handle logging items of this
	 * log Level.
	 *
	 * @param $level
	 *
	 * @return boolean
	 */
	public function canHandle(string $level): bool
	{
		return in_array($level, $this->handles);
	}

	//--------------------------------------------------------------------

	/**
	 * Handles logging the message.
	 * If the handler returns false, then execution of handlers
	 * will stop. Any handlers that have not run, yet, will not
	 * be run.
	 *
	 * @param $level
	 * @param $message
	 *
	 * @return boolean
	 */
	abstract public function handle($level, $message): bool;

	//--------------------------------------------------------------------

	/**
	 * Stores the date format to use while logging messages.
	 *
	 * @param string $format
	 *
	 * @return HandlerInterface
	 */
	public function setDateFormat(string $format): HandlerInterface
	{
		$this->dateFormat = $format;

		return $this;
	}

	//--------------------------------------------------------------------
}
