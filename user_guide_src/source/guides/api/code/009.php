<?php

namespace App\Controllers\Api;

use CodeIgniter\Api\ResponseTrait;
use App\Controllers\BaseController;

class Books extends BaseController
{
    use ResponseTrait;

    protected $format = 'json';

    /**
     * List one or many resources
     * GET /api/books
     *    and
     * GET /api/books/{id}
     */
    public function getIndex(?int $id = null)
    {
        //
    }

    /**
     * Update a book
     *
     * PUT /api/books/{id}
     */
    public function putIndex(int $id)
    {
        //
    }

    /**
     * Create a new book
     *
     * POST /api/books
     */
    public function postIndex()
    {
        //
    }

    /**
     * Delete a book
     *
     * DELETE /api/books/{id}
     */
    public function deleteIndex(int $id)
    {
        //
    }
}
