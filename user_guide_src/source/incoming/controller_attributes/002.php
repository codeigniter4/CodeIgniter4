<?php

namespace App\Controllers;

use CodeIgniter\Router\Attributes\Filter;

#[Filter(by: 'group', having: ['admin', 'superadmin'])]
class AdminController extends BaseController
{
    #[Filter(by: 'permission', having: ['users.manage'])]
    public function users()
    {
        // Will have 'group' filter with ['admin', 'superadmin']
        // and 'permission' filter with ['users.manage']
    }
}
