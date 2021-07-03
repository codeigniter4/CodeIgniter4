<?php

namespace CodeIgniter\Helpers;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class DateHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('date');
    }

    //--------------------------------------------------------------------

    public function testNowDefault()
    {
        $this->assertCloseEnough(now(), time());  // close enough
    }

    //--------------------------------------------------------------------

    public function testNowSpecific()
    {
        // Chicago should be two hours ahead of Vancouver
        $this->assertCloseEnough(7200, now('America/Chicago') - now('America/Vancouver'));
    }
}
