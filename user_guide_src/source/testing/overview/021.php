<?php

namespace Tests;

use CodeIgniter\I18n\Time;
use CodeIgniter\Test\CIUnitTestCase;

final class TimeDependentCodeTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        // Reset the current time.
        Time::setTestNow();
    }

    public function testFixTime(): void
    {
        // Fix the current time to "2023-11-25 12:00:00".
        Time::setTestNow('2023-11-25 12:00:00');

        // This assertion always passes.
        $this->assertSame('2023-11-25 12:00:00', (string) Time::now());
    }
}
