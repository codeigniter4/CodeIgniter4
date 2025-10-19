<?php

namespace App\Controllers;

use App\Transformers\UserTransformer;
use CodeIgniter\API\ResponseTrait;

class Users extends BaseController
{
    use ResponseTrait;

    public function show($id)
    {
        $user = model('UserModel')->find($id);

        if (! $user) {
            return $this->failNotFound('User not found');
        }

        $transformer = new UserTransformer();

        return $this->respond($transformer->transform($user));
    }

    public function index()
    {
        $users = model('UserModel')->findAll();

        $transformer = new UserTransformer();

        return $this->respond($transformer->transformMany($users));
    }
}
