<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

class Users extends \CodeIgniter\Controller
{
    use ResponseTrait;

    public function createUser()
    {
        $model = new UserModel();
        $user  = $model->save($this->request->getPost());

        // Respond with 201 status code
        return $this->respondCreated();
    }
}
