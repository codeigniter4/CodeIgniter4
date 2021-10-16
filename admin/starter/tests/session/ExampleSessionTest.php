<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class ExampleSessionTest extends CIUnitTestCase
{
    public function testSessionSimple()
    {
        $this->session->set('logged_in', 123);
        $this->assertSame(123, $this->session->get('logged_in'));
    }
}
