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

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Model;
use Psr\Log\LoggerInterface;

/**
 * An extendable controller to provide a RESTful API for a resource.
 */
class ResourceController extends Controller
{
	use ResponseTrait;

	/**
	 * Name of the model class managing this resource's data
	 *
	 * @var string
	 */
	protected $modelName;

	/**
	 * The model holding this resource's data
	 *
	 * @var Model
	 */
	protected $model;

	//--------------------------------------------------------------------

	public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
	{
		parent::initController($request, $response, $logger);

		// instantiate our model, if needed
		$this->setModel($this->modelName);
	}

	//--------------------------------------------------------------------

	/**
	 * Return an array of resource objects, themselves in array format
	 *
	 * @return mixed
	 */
	public function index()
	{
		return $this->fail(lang('RESTful.notImplemented', ['index']), 501);
	}

	/**
	 * Return the properties of a resource object
	 *
	 * @return mixed
	 */
	public function show($id = null)
	{
		return $this->fail(lang('RESTful.notImplemented', ['show']), 501);
	}

	/**
	 * Return a new resource object, with default properties
	 *
	 * @return mixed
	 */
	public function new()
	{
		return $this->fail(lang('RESTful.notImplemented', ['new']), 501);
	}

	/**
	 * Create a new resource object, from "posted" parameters
	 *
	 * @return mixed
	 */
	public function create()
	{
		return $this->fail(lang('RESTful.notImplemented', ['create']), 501);
	}

	/**
	 * Return the editable properties of a resource object
	 *
	 * @return mixed
	 */
	public function edit($id = null)
	{
		return $this->fail(lang('RESTful.notImplemented', ['edit']), 501);
	}

	/**
	 * Add or update a model resource, from "posted" properties
	 *
	 * @return mixed
	 */
	public function update($id = null)
	{
		return $this->fail(lang('RESTful.notImplemented', ['update']), 501);
	}

	/**
	 * Delete the designated resource object from the model
	 *
	 * @return mixed
	 */
	public function delete($id = null)
	{
		return $this->fail(lang('RESTful.notImplemented', ['delete']), 501);
	}

	//--------------------------------------------------------------------

	/**
	 * Set or change the model this controller is bound to.
	 * Given either the name or the object, determine the other.
	 *
	 * @param string|object $which
	 */
	public function setModel($which = null)
	{
		// save what we have been given
		if (! empty($which))
		{
			if (is_object($which))
			{
				$this->model = $which;
			}
			else
			{
				$this->modelName = $which;
			}
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
		if (empty($this->modelName) && ! empty($this->model))
		{
			$this->modelName = get_class($this->model);
		}
	}

	/**
	 * Set/change the expected response representation for returned objects
	 *
	 * @param string $format
	 */
	public function setFormat(string $format = 'json')
	{
		if (in_array($format, ['json', 'xml'], true))
		{
			$this->format = $format;
		}
	}
}
