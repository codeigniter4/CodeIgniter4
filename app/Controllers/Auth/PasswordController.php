<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;

class PasswordController extends BaseController
{
    public function forgot()
    {
        return view('auth/forgot_password');
    }

    // Implementation for reset password would go here
}
