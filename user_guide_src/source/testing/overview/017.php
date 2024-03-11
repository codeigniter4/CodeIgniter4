<?php

namespace Tests;

use App\Models\UserModel;
use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Mock\MockUserModel;

final class SomeTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $model = new MockUserModel();
        Factories::injectMock('models', UserModel::class, $model);
    }
}
