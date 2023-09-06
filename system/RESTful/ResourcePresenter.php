<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\RESTful;

use CodeIgniter\HTTP\ResponseInterface;

/**
 * An extendable controller to help provide a UI for a resource.
 *
 * @see \CodeIgniter\RESTful\ResourcePresenterTest
 */
class ResourcePresenter extends BaseResource
{
    /**
     * Present a view of resource objects
     *
     * @return ResponseInterface|string|void
     */
    public function index()
    {
        return lang('RESTful.notImplemented', ['index']);
    }

    /**
     * Present a view to present a specific resource object
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface|string|void
     */
    public function show($id = null)
    {
        return lang('RESTful.notImplemented', ['show']);
    }

    /**
     * Present a view to present a new single resource object
     *
     * @return ResponseInterface|string|void
     */
    public function new()
    {
        return lang('RESTful.notImplemented', ['new']);
    }

    /**
     * Process the creation/insertion of a new resource object.
     * This should be a POST.
     *
     * @return ResponseInterface|string|void
     */
    public function create()
    {
        return lang('RESTful.notImplemented', ['create']);
    }

    /**
     * Present a view to edit the properties of a specific resource object
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface|string|void
     */
    public function edit($id = null)
    {
        return lang('RESTful.notImplemented', ['edit']);
    }

    /**
     * Process the updating, full or partial, of a specific resource object.
     * This should be a POST.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface|string|void
     */
    public function update($id = null)
    {
        return lang('RESTful.notImplemented', ['update']);
    }

    /**
     * Present a view to confirm the deletion of a specific resource object
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface|string|void
     */
    public function remove($id = null)
    {
        return lang('RESTful.notImplemented', ['remove']);
    }

    /**
     * Process the deletion of a specific resource object
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface|string|void
     */
    public function delete($id = null)
    {
        return lang('RESTful.notImplemented', ['delete']);
    }
}
