<?php

declare(strict_types=1);

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

        // User will input "a" (invalid value) and "0".
        $io->setInputs(['a', '0']);

        command('db:table');

        $result = $io->getOutput();

        $expectedPattern = '/Which table do you want to see\? \[0, 1, 2, 3, 4, 5, 6, 7, 8, 9.*?\]: a
The "Which table do you want to see\?" field must be one of: 0, 1, 2, 3, 4, 5, 6, 7, 8, 9.*?./';
        $this->assertMatchesRegularExpression($expectedPattern, $result);

        $expected = 'Data of Table "db_migrations":';
        $this->assertStringContainsString($expected, $result);

        $expectedPattern = '/\| id[[:blank:]]+\| version[[:blank:]]+\| class[[:blank:]]+\| group[[:blank:]]+\| namespace[[:blank:]]+\| time[[:blank:]]+\| batch \|/';
        $this->assertMatchesRegularExpression($expectedPattern, $result);

        // Remove MockInputOutput.
        CLI::resetInputOutput();
    }
}
