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

namespace CodeIgniter\RESTful;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;

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

	const NOT_THERE = 'Action not implemented yet';

	//--------------------------------------------------------------------

	public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	{
		parent::initController($request, $response, $logger);

		// instantiate our model, if needed
		if (! empty($this->modelName))
		{
			try
			{
				$this->model = $this->modelName();
			}
			catch (\Exception $e)
			{
				// ignored. we just own't use a model for now
			}
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Present a view of resource objects
	 *
	 * @return string
	 */
	public function index()
	{
		return 'index: ' . NOT_THERE;
	}

	/**
	 * Present a view to present a specific resource object
	 *
	 * @param  type $id
	 * @return string
	 */
	public function show($id = null)
	{
		return 'show: ' . NOT_THERE;
	}

	/**
	 * Present a view to present a new single resource object
	 *
	 * @param  type $id
	 * @return string
	 */
	public function new()
	{
		return 'new: ' . NOT_THERE;
	}

	/**
	 * Process the creation/insertion of a new resource object
	 *
	 * @return string
	 */
	public function create()
	{
		return 'create: ' . NOT_THERE;
	}

	/**
	 * Present a view to confirm the deletion of a specific resource object
	 *
	 * @param  type $id
	 * @return string
	 */
	public function remove($id = null)
	{
		return 'remove: ' . NOT_THERE;
	}

	/**
	 * Process the deletion of a specific resource object
	 *
	 * @param  type $id
	 * @return string
	 */
	public function delete($id = null)
	{
		return 'delete: ' . NOT_THERE;
	}

	/**
	 * Present a view to edit the properties of a specific resource object
	 *
	 * @param  type $id
	 * @return string
	 */
	public function edit($id = null)
	{
		return 'edit: ' . NOT_THERE;
	}

	/**
	 * Process the updating, full or partial, of a specific resource object
	 *
	 * @param  type $id
	 * @return string
	 */
	public function update($id = null)
	{
		return 'update: ' . NOT_THERE;
	}

	//--------------------------------------------------------------------

	/**
	 * Set/change the model that this controller is bound to
	 *
	 * @param type $which
	 */
	public function setModel($which = null)
	{
		$this->model = $model;
	}

}
