<?php
namespace CodeIgniter\Resource;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;

/**
 * An extendable controller to provide a UI for a resource.
 */
class ResourcePresenter extends Controller
{

	use ResponseTrait;

	/**
	 *
	 * @var \CodeIgniter\Model the model holding this resource's data
	 */
	protected $model = null;

	const NOT_THERE = 'Action not implemented yet';

	//--------------------------------------------------------------------

	/**
	 * Present a view of resource objects
	 * @return string	
	 */
	public function index()
	{
		return NOT_THERE;
	}

	/**
	 * Present a view to present a specific resource object
	 * @param type $id
	 * @return string
	 */
	public function show($id = null)
	{
		return NOT_THERE;
	}

	/**
	 * Present a view to present a new single resource object
	 * @param type $id
	 * @return string
	 */
	public function new()
	{
		return NOT_THERE;
	}

	/**
	 * Process the creation/insertion of a new resource object
	 * @return string
	 */
	public function create()
	{
		return NOT_THERE;
	}

	/**
	 * Present a view to confirm the deletion of a specific resource object
	 * @param type $id
	 * @return string
	 */
	public function remove($id = null)
	{
		return NOT_THERE;
	}

	/**
	 * Process the deletion of a specific resource object
	 * @param type $id
	 * @return string
	 */
	public function delete($id = null)
	{
		return NOT_THERE;
	}

	/**
	 * Present a view to edit the properties of a specific resource object
	 * @param type $id
	 * @return string
	 */
	public function edit($id = null)
	{
		return NOT_THERE;
	}

	/**
	 * Process the updating, full or partial, of a specific resource object
	 * @param type $id
	 * @return string
	 */
	public function update($id = null)
	{
		return NOT_THERE;
	}

	//--------------------------------------------------------------------

	/**
	 * Set/change the model that this controller is bound to
	 * @param type $which
	 */
	public function setModel($which = null)
	{
		$this->model = $model;
	}

}
