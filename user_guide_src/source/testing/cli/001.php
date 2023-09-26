<?php

use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\Mock\MockInputOutput;

final class DbTableTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrateOnce = true;

    public function testDbTable(): void
    {
        // Set MockInputOutput to CLI.
        $io = new MockInputOutput();
        CLI::setInputOutput($io);

        // User will input "a" (invalid value) and "0".
        $io->setInputs(['a', '0']);

        command('db:table');

        // Get the whole output string.
        $output = $io->getOutput();

        $expected = 'Which table do you want to see? [0, 1, 2, 3, 4, 5, 6, 7, 8, 9]: a';
        $this->assertStringContainsString($expected, $output);

        $expected = 'Data of Table "db_migrations":';
        $this->assertStringContainsString($expected, $output);

        // Remove MockInputOutput.
        CLI::resetInputOutput();
    }
}
