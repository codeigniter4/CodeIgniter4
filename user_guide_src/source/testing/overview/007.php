<?php

trait AuthTrait
{
    protected setUpAuthTrait()
    {
        $user = $this->createFakeUser();
        $this->logInUser($user);
    }
    
    // ...
}

class AuthenticationFeatureTest
{
    use AuthTrait;

    // ...
}