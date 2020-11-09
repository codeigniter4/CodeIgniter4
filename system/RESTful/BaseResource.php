<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\RESTful;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class BaseResource extends Controller
{
	/**
	 * @var string The model name that managing this resource's data
	 */
	protected $modelName;
	
	/**
	 * @var string The model that holding this resource's data
	 */
	protected $model;

	//--------------------------------------------------------------------

	public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
	{
		parent::initController($request, $response, $logger);

		if (! empty($this->model))
		{
			$this->model = model($this->model);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Set or change the model this controller is bound to.
	 * Given either the name or the object, determine the other.
	 *
	 * @param string $model
	 *
	 * @return void
	 *
	 * @deprecated
	 */
	public function setModel(string $model)
	{
		if (! empty($this->model))
		{
			$this->model = model($model);
		}
	}
}
