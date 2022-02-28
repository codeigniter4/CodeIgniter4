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

/**
 * @internal
 */
final class AuthenticationFeatureTest
{
    use AuthTrait;

    // ...
}
