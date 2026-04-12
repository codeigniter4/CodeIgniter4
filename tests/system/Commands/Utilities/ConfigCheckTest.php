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

namespace CodeIgniter\Commands\Utilities;

use Closure;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use Config\App;
use Kint\Kint;
use Kint\Renderer\CliRenderer;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class ConfigCheckTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        App::$override = false;

        putenv('NO_COLOR=1');
        CliRenderer::$cli_colors = false;
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        App::$override = true;

        putenv('NO_COLOR');
        CliRenderer::$cli_colors = true;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->resetServices();
        CLI::reset();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->resetServices();
        CLI::reset();
    }

    public function testCommandConfigCheckWithNoArgumentPassed(): void
    {
        command('config:check');

        $this->assertSame(
            <<<'EOF'

                You must specify a Config classname.
                  Usage: config:check <classname>
                Example: config:check App
                         config:check 'CodeIgniter\Shield\Config\Auth'

                EOF,
            $this->getStreamFilterBuffer(),
        );
    }

    public function testCommandConfigCheckNonexistentClass(): void
    {
        command('config:check Nonexistent');

        $this->assertSame(
            "\nNo such Config class: Nonexistent\n",
            $this->getStreamFilterBuffer(),
        );
    }

    public function testConfigCheckWithKintEnabledUsesKintD(): void
    {
        /** @var Closure(mixed...): string */
        $command = self::getPrivateMethodInvoker(
            new ConfigCheck(service('logger'), service('commands')),
            'getKintD',
        );

        command('config:check App');

        $this->assertSame(
            "\n" . $command(config('App')) . "\n",
            preg_replace('/\s+Config Caching: \S+/', '', $this->getStreamFilterBuffer()),
        );
    }

    public function testConfigCheckWithKintDisabledUsesVarDump(): void
    {
        /** @var Closure(mixed...): string */
        $command = self::getPrivateMethodInvoker(
            new ConfigCheck(service('logger'), service('commands')),
            'getVarDump',
        );

        try {
            Kint::$enabled_mode = false;
            command('config:check App');

            $this->assertSame(
                "\n" . $command(config('App')),
                preg_replace('/\s+Config Caching: \S+/', '', $this->getStreamFilterBuffer()),
            );
        } finally {
            Kint::$enabled_mode = true;
        }
    }
}
