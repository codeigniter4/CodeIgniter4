<?php

namespace App\Controllers;

class UserController extends BaseController
{
    public function index()
    {
        // ...

        $pager = service('pager');

        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 20;
        $total   = 200;

        // Call makeLinks() to make pagination links.
        $pager_links = $pager->makeLinks($page, $perPage, $total);

        $data = [
            // ...
            'pager_links' => $pager_links,
        ];

        return view('users/index', $data);
    }
}
?>

<!-- In your view file: -->
<?= $pager_links ?>
