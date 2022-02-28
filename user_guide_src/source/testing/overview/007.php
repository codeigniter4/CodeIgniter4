<?php

trait AuthTrait
{
    protected function setUpAuthTrait()
    {
        $user = $this->createFakeUser();
        $this->logInUser($user);
    }
    // ...
}

final class AuthenticationFeatureTest extends CIUnitTestCase
{
    use AuthTrait;

    // ...
}
