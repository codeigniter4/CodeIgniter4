<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Commands;

use CodeIgniter\CLI\Commands;
use CodeIgniter\Log\Logger;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use Config\Services;
use Tests\Support\Commands\ParamsReveal;

/**
 * @internal
 *
 * @group Others
 */
final class CommandTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    private Logger $logger;
    private Commands $commands;

    protected function setUp(): void
    {
        $this->resetServices();

        parent::setUp();

        $this->logger   = Services::logger();
        $this->commands = Services::commands();
    }

    protected function getBuffer()
    {
        return $this->getStreamFilterBuffer();
    }

    public function testListCommands(): void
    {
        command('list');

        // make sure the result looks like a command list
        $this->assertStringContainsString('Lists the available commands.', $this->getBuffer());
        $this->assertStringContainsString('Displays basic usage information.', $this->getBuffer());
    }

    public function testListCommandsSimple(): void
    {
        command('list --simple');

        $this->assertStringContainsString('db:seed', $this->getBuffer());
        $this->assertStringNotContainsString('Lists the available commands.', $this->getBuffer());
    }

    public function testCustomCommand(): void
    {
        command('app:info');
        $this->assertStringContainsString('CI Version:', $this->getBuffer());
    }

    public function testShowError(): void
    {
        command('app:info');
        $commands = $this->commands->getCommands();
        $command  = new $commands['app:info']['class']($this->logger, $this->commands);

        $command->helpme();
        $this->assertStringContainsString('Displays basic usage information.', $this->getBuffer());
    }

    public function testCommandCall(): void
    {
        command('app:info');
        $commands = $this->commands->getCommands();
        $command  = new $commands['app:info']['class']($this->logger, $this->commands);

        $command->bomb();
        $this->assertStringContainsString('Invalid "background" color:', $this->getBuffer());
    }

    public function testAbstractCommand(): void
    {
        command('app:pablo');
        $this->assertStringContainsString('not found', $this->getBuffer());
    }

    public function testNamespacesCommand(): void
    {
        command('namespaces');

        $this->assertStringContainsString('| Namespace', $this->getBuffer());
        $this->assertStringContainsString('| Config', $this->getBuffer());
        $this->assertStringContainsString('| Yes', $this->getBuffer());
    }

    public function testInexistentCommandWithNoAlternatives(): void
    {
        command('app:oops');
        $this->assertStringContainsString('Command "app:oops" not found', $this->getBuffer());
    }

    public function testInexistentCommandsButWithOneAlternative(): void
    {
        command('namespace');

        $this->assertStringContainsString('Command "namespace" not found.', $this->getBuffer());
        $this->assertStringContainsString('Did you mean this?', $this->getBuffer());
        $this->assertStringContainsString('namespaces', $this->getBuffer());
    }

    public function testInexistentCommandsButWithManyAlternatives(): void
    {
        command('clear');

        $this->assertStringContainsString('Command "clear" not found.', $this->getBuffer());
        $this->assertStringContainsString('Did you mean one of these?', $this->getBuffer());
        $this->assertStringContainsString(':clear', $this->getBuffer());
    }

    /**
     * @dataProvider provideCommandParsesArgsCorrectly
     */
    public function testCommandParsesArgsCorrectly(string $input, array $expected): void
    {
        ParamsReveal::$args = null;
        command($input);

        $this->assertSame($expected, ParamsReveal::$args);
    }

    public static function provideCommandParsesArgsCorrectly(): iterable
    {
        return [
            [
                'reveal as df',
                ['as', 'df'],
            ],
            [
                'reveal',
                [],
            ],
            [
                'reveal seg1 seg2 -opt1 -opt2',
                ['seg1', 'seg2', 'opt1' => null, 'opt2' => null],
            ],
            [
                'reveal seg1 seg2 -opt1 val1 seg3',
                ['seg1', 'seg2', 'opt1' => 'val1', 'seg3'],
            ],
            [
                'reveal as df -gh -jk -qw 12 zx cv',
                ['as', 'df', 'gh' => null, 'jk' => null, 'qw' => '12', 'zx', 'cv'],
            ],
            [
                'reveal as -df "some stuff" -jk 12 -sd "Some longer stuff" -fg \'using single quotes\'',
                ['as', 'df' => 'some stuff', 'jk' => '12', 'sd' => 'Some longer stuff', 'fg' => 'using single quotes'],
            ],
            [
                'reveal as -df "using mixed \'quotes\'\" here\""',
                ['as', 'df' => 'using mixed \'quotes\'" here"'],
            ],
        ];
    }
}
