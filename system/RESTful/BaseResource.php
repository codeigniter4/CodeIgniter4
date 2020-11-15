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

use CodeIgniter\Config\Factories;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseResource extends Controller
{
	/**
	 * @var string|null The model that holding this resource's data
	 */
	protected $modelName;

	/**
	 * @var object|null The model that holding this resource's data
	 */
	protected $model;

	/**
	 * Constructor.
	 *
	 * @param RequestInterface  $request
	 * @param ResponseInterface $response
	 * @param LoggerInterface   $logger
	 */
	public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
	{
		parent::initController($request, $response, $logger);

		// instantiate our model, if needed
		$this->setModel($this->modelName);
	}

	/**
	 * Set or change the model this controller is bound to.
	 * Given either the name or the object, determine the other.
	 *
	 * @param object|string|null $which
	 *
	 * @return void
	 */
	public function setModel($which = null)
	{
		// save what we have been given
		if ($which)
		{
			$this->model     = is_object($which) ? $which : null;
			$this->modelName = is_object($which) ? null : $which;
		}

		// make a model object if needed
		if (empty($this->model) && ! empty($this->modelName))
		{
			if (class_exists($this->modelName))
			{
				$this->model = model($this->modelName);
			}
		}

		// determine model name if needed
		if (! empty($this->model) && empty($this->modelName))
		{
			$this->modelName = get_class($this->model);
		}
	}
}
