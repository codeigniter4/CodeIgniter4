<?php

namespace CodeIgniter\Commands;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;
use Config\Services;
use Tests\Support\Commands\ParamsReveal;

/**
 * @internal
 */
final class CommandTest extends CIUnitTestCase
{
    private $streamFilter;
    protected $logger;
    protected $commands;

    protected function setUp(): void
    {
        parent::setUp();

        CITestStreamFilter::$buffer = '';

        $this->streamFilter = stream_filter_append(STDOUT, 'CITestStreamFilter');
        $this->streamFilter = stream_filter_append(STDERR, 'CITestStreamFilter');

        $this->logger   = Services::logger();
        $this->commands = Services::commands();
    }

    protected function tearDown(): void
    {
        stream_filter_remove($this->streamFilter);
    }

    protected function getBuffer()
    {
        return CITestStreamFilter::$buffer;
    }

    public function testListCommands()
    {
        command('list');

        // make sure the result looks like a command list
        $this->assertStringContainsString('Lists the available commands.', $this->getBuffer());
        $this->assertStringContainsString('Displays basic usage information.', $this->getBuffer());
    }

    public function testListCommandsSimple()
    {
        command('list --simple');

        $this->assertStringContainsString('db:seed', $this->getBuffer());
        $this->assertStringNotContainsString('Lists the available commands.', $this->getBuffer());
    }

    public function testCustomCommand()
    {
        command('app:info');
        $this->assertStringContainsString('CI Version:', $this->getBuffer());
    }

    public function testShowError()
    {
        command('app:info');
        $commands = $this->commands->getCommands();
        $command  = new $commands['app:info']['class']($this->logger, $this->commands);

        $command->helpme();
        $this->assertStringContainsString('Displays basic usage information.', $this->getBuffer());
    }

    public function testCommandCall()
    {
        command('app:info');
        $commands = $this->commands->getCommands();
        $command  = new $commands['app:info']['class']($this->logger, $this->commands);

        $command->bomb();
        $this->assertStringContainsString('Invalid background color:', $this->getBuffer());
    }

    public function testAbstractCommand()
    {
        command('app:pablo');
        $this->assertStringContainsString('not found', $this->getBuffer());
    }

    public function testNamespacesCommand()
    {
        command('namespaces');

        $this->assertStringContainsString('| Namespace', $this->getBuffer());
        $this->assertStringContainsString('| Config', $this->getBuffer());
        $this->assertStringContainsString('| Yes', $this->getBuffer());
    }

    public function testRoutesCommand()
    {
        command('routes');

        $this->assertStringContainsString('| Route', $this->getBuffer());
        $this->assertStringContainsString('| testing', $this->getBuffer());
        $this->assertStringContainsString('\\TestController::index', $this->getBuffer());
    }

    public function testInexistentCommandWithNoAlternatives()
    {
        command('app:oops');
        $this->assertStringContainsString('Command "app:oops" not found', $this->getBuffer());
    }

    public function testInexistentCommandsButWithOneAlternative()
    {
        command('namespace');

        $this->assertStringContainsString('Command "namespace" not found.', $this->getBuffer());
        $this->assertStringContainsString('Did you mean this?', $this->getBuffer());
        $this->assertStringContainsString('namespaces', $this->getBuffer());
    }

    public function testInexistentCommandsButWithManyAlternatives()
    {
        command('clear');

        $this->assertStringContainsString('Command "clear" not found.', $this->getBuffer());
        $this->assertStringContainsString('Did you mean one of these?', $this->getBuffer());
        $this->assertStringContainsString(':clear', $this->getBuffer());
    }

    /**
     * @dataProvider commandArgsProvider
     */
    public function testCommandParsesArgsCorrectly(string $input, array $expected)
    {
        ParamsReveal::$args = null;
        command($input);

        $this->assertSame($expected, ParamsReveal::$args);
    }

    public function commandArgsProvider(): array
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
