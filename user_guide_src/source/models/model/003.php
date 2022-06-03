<?php

namespace App\Models;

use Modules\Authentication\Models\UserAuthModel;

class UserModel extends UserAuthModel
{
    /**
     * Called during initialization. Appends
     * our custom field to the module's model.
     */
    protected function initialize()
    {
        $this->allowedFields[] = 'middlename';
    }
}
