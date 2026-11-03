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
        $io = new MockInputOutput();
        CLI::setInputOutput($io);

        $io->setInputs(['a', '1']);

        command('db:table');

        $output = $io->getOutput();

        $expected = 'Please select correctly.';
        $this->assertStringContainsString($expected, $output);

        $expected = 'Data of "db_migrations" table:';
        $this->assertStringContainsString($expected, $output);

        CLI::resetInputOutput();
    }
}
