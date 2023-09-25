<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Commands\Database;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\Mock\MockInputOutput;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class ShowTableInfoMockIOTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrateOnce = true;

    protected function setUp(): void
    {
        parent::setUp();

        putenv('NO_COLOR=1');
        CLI::init();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        putenv('NO_COLOR');
        CLI::init();
    }

    public function testDbTableWithInputs(): void
    {
        // Set MockInputOutput to CLI.
        $io = new MockInputOutput();
        CLI::setInputOutput($io);

        // User will input "a\n" and "0\n".
        $io->setInputs(["a\n", "0\n"]);

        command('db:table');

        $result = $io->getOutput();

        $expected = 'Data of Table "db_migrations":';
        $this->assertStringContainsString($expected, $result);

        $expectedPattern = '/\| id[[:blank:]]+\| version[[:blank:]]+\| class[[:blank:]]+\| group[[:blank:]]+\| namespace[[:blank:]]+\| time[[:blank:]]+\| batch \|/';
        $this->assertMatchesRegularExpression($expectedPattern, $result);

        // Remove MockInputOutput.
        CLI::resetInputOutput();
    }
}
