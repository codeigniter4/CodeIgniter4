<?php

/**
 * @internal
 */
final class ExampleTest extends \CodeIgniter\Test\CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testIsDefinedAppPath()
    {
        $test = defined('APPPATH');

        $this->assertTrue($test);
    }
}
