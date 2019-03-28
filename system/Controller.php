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
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 3.0.0
 * @filesource
 */

namespace CodeIgniter;

use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Validation\Validation;
use CodeIgniter\Validation\Exceptions\ValidationException;
use Psr\Log\LoggerInterface;

/**
 * Class Controller
 *
 * @package CodeIgniter
 */
class Controller
{

	/**
	 * An array of helpers to be automatically loaded
	 * upon class instantiation.
	 *
	 * @var array
	 */
	protected $helpers = [];

	//--------------------------------------------------------------------

	/**
	 * Instance of the main Request object.
	 *
	 * @var HTTP\IncomingRequest
	 */
	protected $request;

	/**
	 * Instance of the main response object.
	 *
	 * @var HTTP\Response
	 */
	protected $response;

	/**
	 * Instance of logger to use.
	 *
	 * @var Log\Logger
	 */
	protected $logger;

	/**
	 * Whether HTTPS access should be enforced
	 * for all methods in this controller.
	 *
	 * @var integer  Number of seconds to set HSTS header
	 */
	protected $forceHTTPS = 0;

	/**
	 * Once validation has been run,
	 * will hold the Validation instance.
	 *
	 * @var Validation
	 */
	protected $validator;

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 *
	 * @param RequestInterface         $request
	 * @param ResponseInterface        $response
	 * @param \Psr\Log\LoggerInterface $logger
	 *
	 * @throws \CodeIgniter\HTTP\Exceptions\HTTPException
	 */
	public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
	{
		$this->request  = $request;
		$this->response = $response;
		$this->logger   = $logger;
		$this->logger->info('Controller "' . get_class($this) . '" loaded.');

		if ($this->forceHTTPS > 0)
		{
			$this->forceHTTPS($this->forceHTTPS);
		}

		$this->loadHelpers();
	}

	//--------------------------------------------------------------------

	/**
	 * A convenience method to use when you need to ensure that a single
	 * method is reached only via HTTPS. If it isn't, then a redirect
	 * will happen back to this method and HSTS header will be sent
	 * to have modern browsers transform requests automatically.
	 *
	 * @param integer $duration The number of seconds this link should be
	 *                          considered secure for. Only with HSTS header.
	 *                          Default value is 1 year.
	 *
	 * @throws \CodeIgniter\HTTP\Exceptions\HTTPException
	 */
	protected function forceHTTPS(int $duration = 31536000)
	{
		force_https($duration, $this->request, $this->response);
	}

	//--------------------------------------------------------------------

	/**
	 * Provides a simple way to tie into the main CodeIgniter class
	 * and tell it how long to cache the current page for.
	 *
	 * @param integer $time
	 */
	protected function cachePage(int $time)
	{
		CodeIgniter::cache($time);
	}

	//--------------------------------------------------------------------

	/**
	 * Handles "auto-loading" helper files.
	 */
	protected function loadHelpers()
	{
		if (empty($this->helpers))
		{
			return;
		}

		foreach ($this->helpers as $helper)
		{
			helper($helper);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * A shortcut to performing validation on input data. If validation
	 * is not successful, a $errors property will be set on this class.
	 *
	 * @param array|string $rules
	 * @param array        $messages An array of custom error messages
	 *
	 * @return boolean
	 */
	protected function validate($rules, array $messages = []): bool
	{
		$this->validator = Services::validation();

		// If you replace the $rules array with the name of the group
		if (is_string($rules))
		{
			$validation = new \Config\Validation();

			// If the rule wasn't found in the \Config\Validation, we
			// should throw an exception so the developer can find it.
			if (! isset($validation->$rules))
			{
				throw ValidationException::forRuleNotFound($rules);
			}

			// If no error message is defined, use the error message in the Config\Validation file
			if (! $messages)
			{
				$errorName = $rules . '_errors';
				$messages  = $validation->$errorName ?? [];
			}

			$rules = $validation->$rules;
		}

		$success = $this->validator
			->withRequest($this->request)
			->setRules($rules, $messages)
			->run();

		return $success;
	}

	//--------------------------------------------------------------------
}
