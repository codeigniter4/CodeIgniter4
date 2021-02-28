<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class ControllerFactory
 */
class ControllerFactory
{
	/**
	 * @var RequestInterface
	 */
	private $request;

	/**
	 * @var ResponseInterface
	 */
	private $response;
	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * ControllerFactory constructor.
	 *
	 * @param RequestInterface  $request
	 * @param ResponseInterface $response
	 * @param LoggerInterface   $logger
	 */
	public function __construct(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
	{
		$this->request  = $request;
		$this->response = $response;
		$this->logger   = $logger;
	}

	/**
	 * Create a controller instance and initialize it.
	 *
	 * @param  class-string $classname
	 * @return Controller
	 */
	public function create(string $classname): Controller
	{
		/**
		 * @var Controller $controller
		 */
		$controller = new $classname();

		$controller->initController($this->request, $this->response, $this->logger);

		return $controller;
	}
}
