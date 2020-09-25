<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2017 British Columbia Institute of Technology
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
 * @copyright  2014-2017 British Columbia Institute of Technology (https://bcit.ca/)
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Encryption\Exceptions;

use CodeIgniter\Exceptions\ExceptionInterface;
use RuntimeException;

/**
 * Encryption exception
 */
class EncryptionException extends RuntimeException implements ExceptionInterface
{
	/**
	 * Thrown when no driver is present in the active encryption session.
	 *
	 * @return static
	 */
	public static function forNoDriverRequested()
	{
		return new static(lang('Encryption.noDriverRequested'));
	}

	/**
	 * Thrown when the handler requested is not available.
	 *
	 * @param string $handler
	 *
	 * @return static
	 */
	public static function forNoHandlerAvailable(string $handler)
	{
		return new static(lang('Encryption.noHandlerAvailable', [$handler]));
	}

	/**
	 * Thrown when the handler requested is unknown.
	 *
	 * @param string $driver
	 *
	 * @return static
	 */
	public static function forUnKnownHandler(string $driver = null)
	{
		return new static(lang('Encryption.unKnownHandler', [$driver]));
	}

	/**
	 * Thrown when no starter key is provided for the current encryption session.
	 *
	 * @return static
	 */
	public static function forNeedsStarterKey()
	{
		return new static(lang('Encryption.starterKeyNeeded'));
	}

	/**
	 * Thrown during data decryption when a problem or error occurred.
	 *
	 * @return static
	 */
	public static function forAuthenticationFailed()
	{
		return new static(lang('Encryption.authenticationFailed'));
	}

	/**
	 * Thrown during data encryption when a problem or error occurred.
	 *
	 * @return static
	 */
	public static function forEncryptionFailed()
	{
		return new static(lang('Encryption.encryptionFailed'));
	}
}
