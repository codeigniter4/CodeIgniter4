<?php namespace CodeIgniter\View;

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
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	2014-2018 British Columbia Institute of Technology (https://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */
class Plugins
{

	/**
	 * @param array $params
	 *
	 * @return string|\CodeIgniter\HTTP\URI
	 */
	public static function currentURL(array $params = [])
	{
		if ( ! function_exists('current_url'))
			// can't unit test this since it is loaded in CIUnitTestCase setup
			// @codeCoverageIgnoreStart
			helper('url');
			// @codeCoverageIgnoreEnd

		return current_url();
	}

	//--------------------------------------------------------------------

	/**
	 * @param array $params
	 *
	 * @return \CodeIgniter\HTTP\URI|mixed|string
	 */
	public static function previousURL(array $params = [])
	{
		if ( ! function_exists('previous_url'))
			// can't unit test this since it is loaded in CIUnitTestCase setup
			// @codeCoverageIgnoreStart
			helper('url');
			// @codeCoverageIgnoreEnd

		return previous_url();
	}

	//--------------------------------------------------------------------

	/**
	 * @param array $params
	 *
	 * @return string
	 */
	public static function mailto(array $params = [])
	{
		if ( ! function_exists('mailto'))
			// can't unit test this since it is loaded in CIUnitTestCase setup
			// @codeCoverageIgnoreStart
			helper('url');
			// @codeCoverageIgnoreEnd

		$email = $params['email'] ?? '';
		$title = $params['title'] ?? '';
		$attrs = $params['attributes'] ?? '';

		return mailto($email, $title, $attrs);
	}

	//--------------------------------------------------------------------

	/**
	 * @param array $params
	 *
	 * @return string
	 */
	public static function safeMailto(array $params = [])
	{
		if ( ! function_exists('safe_mailto'))
			// can't unit test this since it is loaded in CIUnitTestCase setup
			// @codeCoverageIgnoreStart
			helper('url');
			// @codeCoverageIgnoreEnd

		$email = $params['email'] ?? '';
		$title = $params['title'] ?? '';
		$attrs = $params['attributes'] ?? '';

		return safe_mailto($email, $title, $attrs);
	}

	//--------------------------------------------------------------------

	/**
	 * @param array $params
	 *
	 * @return string
	 */
	public static function lang(array $params = [])
	{
		$line = array_shift($params);

		return lang($line, $params);
	}

	//--------------------------------------------------------------------

	/**
	 * @param array $params
	 *
	 * @return string
	 */
	public static function ValidationErrors(array $params = [])
	{

		$validator = \config\services::validation();
		if (empty($params))
		{
			return $validator->listErrors();
		}

		return $validator->showError($params['field']);
	}

}
