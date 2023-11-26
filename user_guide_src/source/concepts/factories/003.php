<?php

use CodeIgniter\Config\Factories;

class SomeOtherClass
{
    public function someFunction()
    {
        $users = Factories::models('UserModel');

        // ...
    }
}
