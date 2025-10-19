<?php

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Transformers\UserTransformer;
use CodeIgniter\API\ResponseTrait;

class UserController extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        $model = model(UserModel::class);

        return $this->paginate(resource: $model, perPage: 20, transformWith: UserTransformer::class);
    }
}
