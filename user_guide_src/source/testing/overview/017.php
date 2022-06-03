<?php

use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;

final class SomeTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $model = new MockUserModel();
        Factories::injectMock('models', 'App\Models\UserModel', $model);
    }
}
