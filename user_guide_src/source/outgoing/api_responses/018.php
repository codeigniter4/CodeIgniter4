<?php

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;

class UserController extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        $model = model(UserModel::class)
            ->where('active', 1);

        return $this->paginate($model, 20);
    }
}
