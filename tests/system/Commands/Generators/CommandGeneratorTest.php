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

namespace CodeIgniter\Commands\Generators;

use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\Commands;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockInputOutput;
use CodeIgniter\Test\StreamFilterTrait;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class CommandGeneratorTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    protected function setUp(): void
    {
        parent::setUp();

        CLI::reset();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        CLI::reset();

        foreach (['Deliver.php', 'Bogus.php', 'PublishCommand.php'] as $file) {
            if (is_file(APPPATH . 'Commands/' . $file)) {
                unlink(APPPATH . 'Commands/' . $file);
            }
        }
    }

    private function getUndecoratedBuffer(): string
    {
        return preg_replace('/\e\[[^m]+m/', '', $this->getStreamFilterBuffer()) ?? '';
    }

    private function getContents(string $file): string
    {
        $contents = file_get_contents(APPPATH . 'Commands/' . $file);
        $this->assertIsString($contents);

        return $contents;
    }

    public function testGenerateCommand(): void
    {
        command('make:command deliver');

        $this->assertSame("\nFile created: APPPATH/Commands/Deliver.php\n", $this->getUndecoratedBuffer());

        $contents = $this->getContents('Deliver.php');
        $this->assertStringContainsString("#[Command(name: 'command:name', description: '', group: 'App')]", $contents);
        $this->assertStringContainsString('class Deliver extends AbstractCommand', $contents);
        $this->assertStringContainsString('protected function execute(array $arguments, array $options): int', $contents);
    }

    public function testGenerateCommandWithOptionCommand(): void
    {
        command('make:command deliver --command clear:sessions');

        $this->assertStringContainsString(
            "#[Command(name: 'clear:sessions', description: '', group: 'App')]",
            $this->getContents('Deliver.php'),
        );
    }

    public function testGenerateCommandWithOptionTypeGenerator(): void
    {
        command('make:command deliver --type generator');

        $contents = $this->getContents('Deliver.php');
        $this->assertStringContainsString("#[Command(name: 'command:name', description: '', group: 'Generators')]", $contents);
        $this->assertStringContainsString(
            "#[GeneratorCommand(component: 'Command', template: 'command.tpl.php', directory: 'Commands')]",
            $contents,
        );
        $this->assertStringContainsString('class Deliver extends AbstractGeneratorCommand', $contents);
        $this->assertStringNotContainsString('protected function execute', $contents);
    }

    public function testGenerateCommandWithOptionGroup(): void
    {
        command('make:command deliver --group Deliverables');

        $this->assertStringContainsString(
            "#[Command(name: 'command:name', description: '', group: 'Deliverables')]",
            $this->getContents('Deliver.php'),
        );
    }

    public function testGenerateCommandWithShortcuts(): void
    {
        command('make:command deliver -c make:deliverable -t generator -g Deliverables');

        $contents = $this->getContents('Deliver.php');
        $this->assertStringContainsString("#[Command(name: 'make:deliverable', description: '', group: 'Deliverables')]", $contents);
        $this->assertStringContainsString('class Deliver extends AbstractGeneratorCommand', $contents);
    }

    public function testInvalidTypeIsRejectedWhenNotInteractive(): void
    {
        command('make:command bogus --type advanced --no-interaction');

        $this->assertSame("\nCommand type \"advanced\" is not valid.\n", $this->getUndecoratedBuffer());
        $this->assertFileDoesNotExist(APPPATH . 'Commands/Bogus.php');
    }

    public function testInvalidTypePromptsWhenInteractive(): void
    {
        $io = new MockInputOutput();
        $io->setInputs(['generator']);
        CLI::setInputOutput($io);

        $command = new CommandGenerator(new Commands());
        $command->setInteractive(true);

        $this->assertSame(EXIT_SUCCESS, $command->run(['deliver'], ['type' => 'advanced']));
        $this->assertStringContainsString('Command type', $io->getOutput());
        $this->assertStringContainsString('class Deliver extends AbstractGeneratorCommand', $this->getContents('Deliver.php'));
    }

    public function testGenerateCommandWithOptionSuffix(): void
    {
        command('make:command publish --suffix');

        $this->assertFileExists(APPPATH . 'Commands/PublishCommand.php');
    }
}
