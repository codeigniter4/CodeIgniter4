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

namespace CodeIgniter\HTTP;

use CodeIgniter\HTTP\Exceptions\HTTPException;
use Config\Services;

/**
 * Handle a redirect response
 */
class RedirectResponse extends Response
{
	/**
	 * Sets the URI to redirect to and, optionally, the HTTP status code to use.
	 * If no code is provided it will be automatically determined.
	 *
	 * @param string       $uri    The URI to redirect to
	 * @param integer|null $code   HTTP status code
	 * @param string       $method
	 *
	 * @return $this
	 */
	public function to(string $uri, int $code = null, string $method = 'auto')
	{
		// If it appears to be a relative URL, then convert to full URL
		// for better security.
		if (strpos($uri, 'http') !== 0)
		{
			$url = current_url(true)->resolveRelativeURI($uri);
			$uri = (string)$url;
		}

		return $this->redirect($uri, $method, $code);
	}

	/**
	 * Sets the URI to redirect to but as a reverse-routed or named route
	 * instead of a raw URI.
	 *
	 * @param string       $route
	 * @param array        $params
	 * @param integer|null $code
	 * @param string       $method
	 *
	 * @return $this
	 */
	public function route(string $route, array $params = [], int $code = 302, string $method = 'auto')
	{
		$routes = Services::routes(true);

		$route = $routes->reverseRoute($route, ...$params);

		if (! $route)
		{
			throw HTTPException::forInvalidRedirectRoute($route);
		}

		return $this->redirect(site_url($route), $method, $code);
	}

	/**
	 * Helper function to return to previous page.
	 *
	 * Example:
	 *  return redirect()->back();
	 *
	 * @param integer|null $code
	 * @param string       $method
	 *
	 * @return $this
	 */
	public function back(int $code = null, string $method = 'auto')
	{
		$this->ensureSession();

		return $this->redirect(previous_url(), $method, $code);
	}

	/**
	 * Specifies that the current $_GET and $_POST arrays should be
	 * packaged up with the response. It will then be available via
	 * the 'old()' helper function.
	 *
	 * @return $this
	 */
	public function withInput()
	{
		$session = $this->ensureSession();

		$input = [
			'get'  => $_GET ?? [],
			'post' => $_POST ?? [],
		];

		$session->setFlashdata('_ci_old_input', $input);

		// If the validator has any errors, transmit those back
		// so they can be displayed when the validation is
		// handled within a method different than displaying the form.
		$validator = Services::validation();
		if (! empty($validator->getErrors()))
		{
			$session->setFlashdata('_ci_validation_errors', serialize($validator->getErrors()));
		}

		return $this;
	}

	/**
	 * Adds a key and message to the session as Flashdata.
	 *
	 * @param string       $key
	 * @param string|array $message
	 *
	 * @return $this
	 */
	public function with(string $key, $message)
	{
		$session = $this->ensureSession();

		$session->setFlashdata($key, $message);

		return $this;
	}

	/**
	 * Ensures the session is loaded and started.
	 *
	 * @return \CodeIgniter\Session\Session
	 */
	protected function ensureSession()
	{
		return Services::session();
	}
}
