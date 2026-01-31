<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\Api\ResponseTrait;

class Books extends BaseController
{
    use ResponseTrait;

    /**
     * List one or many resources
     * GET /api/books
     *    and
     * GET /api/books/{id}
     */
    public function getIndex(?int $id = null)
    {
    }

    /**
     * Update a book
     *
     * PUT /api/books/{id}
     */
    public function putIndex(int $id)
    {
    }

    /**
     * Create a new book
     *
     * POST /api/books
     */
    public function postIndex()
    {
    }

    /**
     * Delete a book
     *
     * DELETE /api/books/{id}
     */
    public function deleteIndex(int $id)
    {
    }
}
