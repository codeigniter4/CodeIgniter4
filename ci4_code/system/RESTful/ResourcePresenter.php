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

/**
 * An extendable controller to help provide a UI for a resource.
 */
class ResourcePresenter extends BaseResource
{
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
	 * @param mixed $id
	 *
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
	 * Present a view to edit the properties of a specific resource object
	 *
	 * @param mixed $id
	 *
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
	 * @param mixed $id
	 *
	 * @return mixed
	 */
	public function update($id = null)
	{
		return lang('RESTful.notImplemented', ['update']);
	}

	/**
	 * Present a view to confirm the deletion of a specific resource object
	 *
	 * @param mixed $id
	 *
	 * @return mixed
	 */
	public function remove($id = null)
	{
		return lang('RESTful.notImplemented', ['remove']);
	}

	/**
	 * Process the deletion of a specific resource object
	 *
	 * @param mixed $id
	 *
	 * @return mixed
	 */
	public function delete($id = null)
	{
		return lang('RESTful.notImplemented', ['delete']);
	}
}
