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
        App::$override = false;

        putenv('NO_COLOR=1');
        CliRenderer::$cli_colors = false;

        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass(): void
    {
        App::$override = true;

        putenv('NO_COLOR');
        CliRenderer::$cli_colors = true;

        parent::tearDownAfterClass();
    }

    protected function setUp(): void
    {
        $this->resetServices();
        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->resetServices();
        parent::tearDown();
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
            str_replace("\n\n", "\n", $this->getStreamFilterBuffer()),
        );
    }

    public function testCommandConfigCheckNonexistentClass(): void
    {
        command('config:check Nonexistent');

        $this->assertSame(
            "No such Config class: Nonexistent\n",
            $this->getStreamFilterBuffer(),
        );
    }

    public function testConfigCheckWithKintEnabledUsesKintD(): void
    {
        /** @var Closure(object): string $command */
        $command = $this->getPrivateMethodInvoker(
            new ConfigCheck(service('logger'), service('commands')),
            'getKintD',
        );

        command('config:check App');

        $this->assertSame(
            $command(config('App')) . "\n",
            preg_replace('/\s+Config Caching: \S+/', '', $this->getStreamFilterBuffer()),
        );
    }

    public function testConfigCheckWithKintDisabledUsesVarDump(): void
    {
        /** @var Closure(object): string $command */
        $command = $this->getPrivateMethodInvoker(
            new ConfigCheck(service('logger'), service('commands')),
            'getVarDump',
        );
        $clean = static fn (string $input): string => trim(preg_replace(
            '/(\033\[[0-9;]+m)|(\035\[[0-9;]+m)/u',
            '',
            $input,
        ));

        try {
            Kint::$enabled_mode = false;
            command('config:check App');

            $this->assertSame(
                $clean($command(config('App'))),
                $clean(preg_replace('/\s+Config Caching: \S+/', '', $this->getStreamFilterBuffer())),
            );
        } finally {
            Kint::$enabled_mode = true;
        }
    }
}
