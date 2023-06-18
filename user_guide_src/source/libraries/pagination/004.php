<?php

namespace App\Controllers;

class UserController extends BaseController
{
    public function index()
    {
        $userModel = new \App\Models\UserModel();
        $pageModel = new \App\Models\PageModel();

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
