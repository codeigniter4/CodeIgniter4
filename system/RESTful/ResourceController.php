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
 * An extendable controller to provide a RESTful API for a resource.
 *
 * @package CodeIgniter\RESTful
 */
class ResourceController extends Controller
{

	use ResponseTrait;

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

	/**
	 *
	 * @var string the representation format to return resource data in (json/xml)
	 */
	protected $format = 'json';

	//--------------------------------------------------------------------

	public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	{
		parent::initController($request, $response, $logger);

		// instantiate our model, if needed
		if (empty($this->model) && ! empty($this->modelName))
		{
			try
			{
				$this->model = new $this->modelName;
			}
			catch (\Exception $e)
			{
				// ignored. we just don't use a model for now
			}
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Return an array of resource objects, themselves in array format
	 *
	 * @return array	an array
	 */
	public function index()
	{
		return $this->fail('Action not implemented', 501);
	}

	/**
	 * Return the properties of a resource object
	 *
	 * @return array	an array
	 */
	public function show($id = null)
	{
		return $this->fail('Action not implemented', 501);
	}

	/**
	 * Return a new resource object, with default properties
	 *
	 * @return array	an array
	 */
	public function new()
	{
		return $this->fail('Action not implemented', 501);
	}

	/**
	 * Create a new resource object, from "posted" parameters
	 *
	 * @return array	an array
	 */
	public function create()
	{
		return $this->fail('Action not implemented', 501);
	}

	/**
	 * Return the editable properties of a resource object
	 *
	 * @return array	an array
	 */
	public function edit($id = null)
	{
		return $this->fail('Action not implemented', 501);
	}

	/**
	 * Add or update a model resource, from "posted" properties
	 *
	 * @return array	an array
	 */
	public function update($id = null)
	{
		return $this->fail('Action not implemented', 501);
	}

	/**
	 * Delete the designated resource object from the model
	 *
	 * @return array	an array
	 */
	public function delete($id = null)
	{
		return $this->fail('Action not implemented', 501);
	}

	//--------------------------------------------------------------------

	/**
	 * Set or change the model this controller is bound to
	 *
	 * @param string|\CodeIgniter\Model $which
	 */
	public function setModel($which = null)
	{
		if (! empty($which))
		{
			if ($which instanceof \CodeIgniter\Model)
			{
				$this->model = $which;
			}
			else
			{
				$this->modelName = $which;
			}
		}
	}

	/**
	 * Set/change the expected response representation for returned objects
	 *
	 * @param string $format
	 */
	public function setFormat(string $format = 'json')
	{
		if (in_array($format, ['json', 'xml']))
		{
			$this->format = $format;
		}
	}

}
