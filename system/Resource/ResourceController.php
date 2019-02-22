<?php
namespace CodeIgniter\Resource;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;

/**
 * An extendable controller to provide a RESTful API for a resource.
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
	 * Return an array of resource objects, themselves in array format
	 *
	 * @return array	an array
	 */
	public function index()
	{
		if ($this->model instanceof \CodeIgniter\Model)
		{
			$this->respond($this->model->findAll());
		}
		$this->fail('Action not implemented', 501);
	}

	/**
	 * Return a new resource object, with default properties
	 *
	 * @return array	an array
	 */
	public function new()
	{
		$this->fail('Action not implemented', 501);
	}

	/**
	 * Return the editable properties of a resource object
	 *
	 * @return array	an array
	 */
	public function edit($id = null)
	{
		$this->fail('Action not implemented', 501);
	}

	/**
	 * Return the properties of a resource object
	 *
	 * @return array	an array
	 */
	public function show($id = null)
	{
		if ($this->model instanceof \CodeIgniter\Model)
		{
			$this->respond($this->model->find($id));
		}
		$this->fail('Action not implemented', 501);
	}

	/**
	 * Create a new resource object, from "posted" parameters
	 *
	 * @return array	an array
	 */
	public function create()
	{
		$this->fail('Action not implemented', 501);
	}

	/**
	 * Delete the designated resource object from the model
	 *
	 * @return array	an array
	 */
	public function delete($id = null)
	{
		$this->fail('Action not implemented', 501);
	}

	/**
	 * Add or update a model resource, from "posted" properties
	 *
	 * @return array	an array
	 */
	public function update($id = null)
	{
		$this->fail('Action not implemented', 501);
	}

	//--------------------------------------------------------------------

	/**
	 * Set or change the model this controller is bound to
	 *
	 * @param string|\CodeIgniter\Model $which
	 */
	public function setModel($which = null)
	{
		$this->model = $model;
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
