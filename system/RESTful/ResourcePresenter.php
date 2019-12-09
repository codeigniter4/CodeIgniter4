<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019 CodeIgniter Foundation
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
 * @copyright  2019 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 3.0.0
 * @filesource
 */

namespace CodeIgniter\RESTful;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * An extendable controller to help provide a UI for a resource.
 *
 * @package CodeIgniter\RESTful
 */
class ResourcePresenter extends Controller
{

	/**
	 *
	 * @var string Name of the model class managing this resource's data
	 */
	protected $modelName = null;

	/**
	 *
	 * @var \CodeIgniter\Model the model holding this resource's data
	 */
	protected $model = null;

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
	 * @return string
	 */
	public function index()
	{
		return lang('RESTful.notImplemented', ['index']);
	}

	/**
	 * Present a view to present a specific resource object
	 *
	 * @param  type $id
	 * @return string
	 */
	public function show($id = null)
	{
		return lang('RESTful.notImplemented', ['show']);
	}

	/**
	 * Present a view to present a new single resource object
	 *
	 * @param  type $id
	 * @return string
	 */
	public function new()
	{
		return lang('RESTful.notImplemented', ['new']);
	}

	/**
	 * Process the creation/insertion of a new resource object.
	 * This should be a POST.
	 *
	 * @return string
	 */
	public function create()
	{
		return lang('RESTful.notImplemented', ['create']);
	}

	/**
	 * Present a view to confirm the deletion of a specific resource object
	 *
	 * @param  type $id
	 * @return string
	 */
	public function remove($id = null)
	{
		return lang('RESTful.notImplemented', ['remove']);
	}

	/**
	 * Process the deletion of a specific resource object
	 *
	 * @param  type $id
	 * @return string
	 */
	public function delete($id = null)
	{
		return lang('RESTful.notImplemented', ['delete']);
	}

	/**
	 * Present a view to edit the properties of a specific resource object
	 *
	 * @param  type $id
	 * @return string
	 */
	public function edit($id = null)
	{
		return lang('RESTful.notImplemented', ['edit']);
	}

	/**
	 * Process the updating, full or partial, of a specific resource object.
	 * This should be a POST.
	 *
	 * @param  type $id
	 * @return string
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
				$this->model = new $this->modelName;
			}
		}

		// determine model name if needed
		if (empty($this->modelName) && ! empty($this->model))
		{
			$this->modelName = get_class($this->model);
		}
	}

}
