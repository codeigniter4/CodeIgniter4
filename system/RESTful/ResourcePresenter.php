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
use CodeIgniter\Model;
use Psr\Log\LoggerInterface;

/**
 * An extendable controller to help provide a UI for a resource.
 */
class ResourcePresenter extends Controller
{

	/**
	 * @var string|null Name of the model class managing this resource's data
	 */
	protected $modelName;

	/**
	 * @var Model|null the model holding this resource's data
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
	 * Present a view of resource objects
	 *
	 * @return mixed
	 */
	public function index()
	{
		return lang('RESTful.notImplemented', ['index']);
	}

	/**
	 * Present a view to present a specific resource object
	 *
	 * @param  mixed $id
	 * @return mixed
	 */
	public function show($id = null)
	{
		return lang('RESTful.notImplemented', ['show']);
	}

	/**
	 * Present a view to present a new single resource object
	 *
	 * @return mixed
	 */
	public function new()
	{
		return lang('RESTful.notImplemented', ['new']);
	}

	/**
	 * Process the creation/insertion of a new resource object.
	 * This should be a POST.
	 *
	 * @return mixed
	 */
	public function create()
	{
		return lang('RESTful.notImplemented', ['create']);
	}

	/**
	 * Present a view to confirm the deletion of a specific resource object
	 *
	 * @param  mixed $id
	 * @return mixed
	 */
	public function remove($id = null)
	{
		return lang('RESTful.notImplemented', ['remove']);
	}

	/**
	 * Process the deletion of a specific resource object
	 *
	 * @param  mixed $id
	 * @return mixed
	 */
	public function delete($id = null)
	{
		return lang('RESTful.notImplemented', ['delete']);
	}

	/**
	 * Present a view to edit the properties of a specific resource object
	 *
	 * @param  mixed $id
	 * @return mixed
	 */
	public function edit($id = null)
	{
		return lang('RESTful.notImplemented', ['edit']);
	}

	/**
	 * Process the updating, full or partial, of a specific resource object.
	 * This should be a POST.
	 *
	 * @param  mixed $id
	 * @return mixed
	 */
	public function update($id = null)
	{
		return lang('RESTful.notImplemented', ['update']);
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
				$this->model     = $which;
				$this->modelName = null;
			}
			else
			{
				$this->model     = null;
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

}
