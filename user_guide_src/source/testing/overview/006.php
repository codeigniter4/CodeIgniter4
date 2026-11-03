<?php

namespace App\Traits;

trait AuthTrait
{
    protected function setUpAuthTrait()
    {
        $user = $this->createFakeUser();
        $this->logInUser($user);
    }

    // ...
}
