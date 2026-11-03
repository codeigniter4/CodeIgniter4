<?php

namespace App\Controllers;

use App\Transformers\UserTransformer;
use CodeIgniter\API\ResponseTrait;

class Users extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        $users = model('UserModel')->findAll();

        $transformer = new UserTransformer();
        $data        = $transformer->transformMany($users);

        return $this->respond($data);
    }
}
