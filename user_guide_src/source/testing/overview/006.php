<?php

final class OneOfMyModelsTest extends CIUnitTestCase
{
    protected $tearDownMethods = [
        'purgeRows',
    ];

    protected function purgeRows()
    {
        $this->model->purgeDeleted();
    }
}
