<?php

protected function setUp()
{
    parent::setUp();

    $model = new MockUserModel();
    Factories::injectMock('models', 'App\Models\UserModel', $model);
}
