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
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class ShowTableInfoMockIOTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrateOnce = true;

    protected function setUp(): void
    {
        parent::setUp();

        CLI::reset();

        putenv('NO_COLOR=1');
        CLI::init();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        CLI::reset();

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

        $this->assertMatchesRegularExpression(
            '/Which table do you want to see\? \[[\d,\s]+\]\: a/',
            $result,
        );
        $this->assertMatchesRegularExpression(
            '/The "Which table do you want to see\?" field must be one of: [\d,\s]+./',
            $result,
        );
        $this->assertMatchesRegularExpression(
            '/Which table do you want to see\? \[[\d,\s]+\]\: 0/',
            $result,
        );
        $this->assertMatchesRegularExpression(
            '/Data of Table "db_migrations"\:/',
            $result,
        );
        $this->assertMatchesRegularExpression(
            '/\| id[[:blank:]]+\| version[[:blank:]]+\| class[[:blank:]]+\| group[[:blank:]]+\| namespace[[:blank:]]+\| time[[:blank:]]+\| batch \|/',
            $result,
        );
    }
}
