<?php

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class UserController extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        $builder = db_connect()
            ->table('users')
            ->where('active', 1);

        return $this->paginate(resource: $builder, perPage: 20);
    }
}
