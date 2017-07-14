<?php namespace CodeIgniter;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2017 British Columbia Institute of Technology
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
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	2014-2017 British Columbia Institute of Technology (https://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */
/**
 * Custom Exceptions
 *
 * These exceptions are provided for your use, and work to provide a consistent
 * experience across the application. The default error codes are already set,
 * and will be used to provide both HTTP status codes and CLI exit codes.
 *
 * The Error Exceptions below are primarily to provide a way to have
 * information logged automatically by the application's log system.
 *
 * @package CodeIgniter
 */

/**
 * Error: system is unusable
 */
class EmergencyError extends \Error
{
	
}

/**
 * Error: Action must be taken immediately (system/db down, etc)
 */
class AlertError extends \Error
{
	
}

/**
 * Error: Critical conditions, like component unavailble, etc.
 */
class CriticalError extends \Error
{
	
}

/**
 * Error: Runtime errors that do not require immediate action
 */
class Error extends \Error
{
	
}

/**
 * Exception for automatic logging.
 */
class PageNotFoundException extends \OutOfBoundsException
{

	/**
	 * Error code
	 * @var int
	 */
	protected $code = 404;

}

/**
 * Exception for automatic logging.
 */
class ConfigException extends CriticalError
{

	/**
	 * Error code
	 * @var int
	 */
	protected $code = 3;

}

/**
 * Exception for automatic logging.
 */
class UnknownFileException extends CriticalError
{

	/**
	 * Error code
	 * @var int
	 */
	protected $code = 4;

}

/**
 * Exception for automatic logging.
 */
class UnknownClassException extends CriticalError
{

	/**
	 * Error code
	 * @var int
	 */
	protected $code = 5;

}

/**
 * Exception for automatic logging.
 */
class UnknownMethodException extends CriticalError
{

	/**
	 * Error code
	 * @var int
	 */
	protected $code = 6;

}

/**
 * Exception for automatic logging.
 */
class UserInputException extends \OutOfBoundsException
{

	/**
	 * Error code
	 * @var int
	 */
	protected $code = 7;

}

/**
 * Exception for automatic logging.
 */
class DatabaseException extends Error
{

	/**
	 * Error code
	 * @var int
	 */
	protected $code = 8;

}
