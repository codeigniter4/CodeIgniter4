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

use CodeIgniter\Test\CIUnitTestCase;

final class AuthenticationFeatureTest extends CIUnitTestCase
{
    use AuthTrait;

    // ...
}
