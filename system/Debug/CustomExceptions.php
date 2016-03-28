<?php namespace CodeIgniter;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
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
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */

/**
 * Custom Exceptions
 *
 * These exceptions are provided for you use, and work to provide a consistent
 * experience across the application. The default error codes are already set,
 * and will be used to provide both HTTP status codes and CLI exit codes.
 *
 * @package CodeIgniter
 */


/*
	The Error Exceptions below are primarily to provide a way to have information logged
	automatically by the application's log system.
*/
class EmergencyError extends \Error {};  // system is unusable
class AlertError     extends \Error {};  // Action must be taken immediately (system/db down, etc)
class CriticalError  extends \Error {};  // Critical conditions, like component unavailble, etc.
class Error          extends \Error {};  // Runtime errors that do not require immediate action

class PageNotFoundException  extends \OutOfBoundsException { protected $code = 404; };
class ConfigException        extends CriticalError         { protected $code = 3; };
class UnknownFileException   extends CriticalError         { protected $code = 4; };
class UnknownClassException  extends CriticalError         { protected $code = 5; };
class UnknownMethodException extends CriticalError         { protected $code = 6; };
class UserInputException     extends \OutOfBoundsException { protected $code = 7; };
class DatabaseException      extends Error                 { protected $code = 8; };
