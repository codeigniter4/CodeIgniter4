<?php

use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\PhpStreamWrapper;

final class SomeTest extends CIUnitTestCase
{
    public function testPrompt(): void
    {
        // Register the PhpStreamWrapper.
        PhpStreamWrapper::register();

        // Set the user input to 'red'. It will be provided as `php://stdin` output.
        $expected = 'red';
        PhpStreamWrapper::setContent($expected);

        $output = CLI::prompt('What is your favorite color?');

        $this->assertSame($expected, $output);

        // Restore php protocol wrapper.
        PhpStreamWrapper::restore();
    }
}
