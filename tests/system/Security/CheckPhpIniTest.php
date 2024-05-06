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

namespace CodeIgniter\Security;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockInputOutput;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class CheckPhpIniTest extends CIUnitTestCase
{
    public function testCheckIni(): void
    {
        $output = CheckPhpIni::checkIni();

        $expected = [
            'global'      => '',
            'current'     => '1',
            'recommended' => '0',
            'remark'      => '',
        ];
        $this->assertSame($expected, $output['display_errors']);
    }

    public function testRunCli(): void
    {
        // Set MockInputOutput to CLI.
        $io = new MockInputOutput();
        CLI::setInputOutput($io);

        CheckPhpIni::run(true);

        // Get the whole output string.
        $output = $io->getOutput();

        $this->assertStringContainsString('display_errors', $output);

        // Remove MockInputOutput.
        CLI::resetInputOutput();
    }

    public function testRunWeb(): void
    {
        $output = CheckPhpIni::run(false);

        $this->assertStringContainsString('display_errors', $output);
    }
}
