<?php

use CodeIgniter\Test\CIUnitTestCase;

final class MyTestClass extends CIUnitTestCase
{
    public function testUserAccess()
    {
        $user = fake('App\Models\UserModel');

        $this->assertTrue($this->userHasAccess($user));
    }
}
