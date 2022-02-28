<?php

final class MyTestClass extends \CodeIgniter\Test\CIUnitTestCase
{
    public function testUserAccess()
    {
        $user = fake('App\Models\UserModel');

        $this->assertTrue($this->userHasAccess($user));
    }
}
