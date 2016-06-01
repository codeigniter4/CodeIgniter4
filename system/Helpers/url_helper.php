<?php

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


if (! function_exists('site_url'))
{
	/**
	 * Return a site URL to use in views
	 * 
	 * @param string      $path
	 * @param string|null $scheme
	 *
	 * @return string
	 */
	function site_url(string $path = '', string $scheme = null): string
	{
		$config = new \Config\App();

		$url = ! empty($config->baseURL)
			? new \CodeIgniter\HTTP\URI($config->baseURL)
			: clone(\CodeIgniter\Services::request()->uri);

		// Add index page
		if (! empty($config->indexPage))
		{
			$path = rtrim($config->indexPage, '/').'/'.$path;
		}

		$url->setPath($path);

		if (! empty($scheme))
		{
			$url->setScheme($scheme);
		}

		return (string)$url;
	}
}

//--------------------------------------------------------------------

if (! function_exists('base_url'))
{
	/**
	 * Return the base URL to use in views
	 * 
	 * @param string $path
	 * @param string $scheme
	 * @return string
	 */
	function base_url(string $path = '', string $scheme = null): string
	{
		$config = new \Config\App();

		$url = ! empty($config->baseURL)
			? new \CodeIgniter\HTTP\URI($config->baseURL)
			: clone(\CodeIgniter\Services::request()->uri);

		$url->setPath($path);

		if (! empty($scheme))
		{
			$url->setScheme($scheme);
		}

		return (string)$url;
	}
}

//--------------------------------------------------------------------

if (! function_exists('current_url'))
{
	/**
	 * Current URL
	 *
	 * Returns the full URL (including segments) of the page where this
	 * function is placed
	 *
	 * @return	string
	 */
	function current_url(bool $returnObject = false)
	{
		return $returnObject === true
			? \CodeIgniter\Services::request()->uri
			: (string)\CodeIgniter\Services::request()->uri;
	}
}
//--------------------------------------------------------------------

if (! function_exists('uri_string'))
{
	/**
	 * URL String
	 *
	 * Returns the URI segments.
	 *
	 * @return	string
	 */
	function uri_string(): string
	{
		return \CodeIgniter\Services::request()->uri->getPath();
	}
}

//--------------------------------------------------------------------

if (! function_exists('index_page'))
{
	/**
	 * Index page
	 *
	 * Returns the "index_page" from your config file
	 *
	 * @return	string
	 */
	function index_page()
	{
		$config = new \Config\App();

		return $config->indexPage;
	}
}
