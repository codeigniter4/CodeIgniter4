<?php

namespace App\Controllers;

use App\Models\PageModel;
use App\Models\UserModel;

class UserController extends BaseController
{
    public function index()
    {
        $userModel = model(UserModel::class);
        $pageModel = model(PageModel::class);

        $data = [
            'users' => $userModel->paginate(10, 'group1'),
            'pages' => $pageModel->paginate(15, 'group2'),
            'pager' => $userModel->pager,
        ];

        echo view('users/index', $data);
    }
}
?>

<!-- In your view file: -->
<?= $pager->links('group1') ?>
<?= $pager->simpleLinks('group2') ?>
