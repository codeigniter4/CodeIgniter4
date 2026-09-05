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

namespace CodeIgniter\CLI;

use CodeIgniter\Exceptions\LogicException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockInputOutput;
use CodeIgniter\Test\StreamFilterTrait;
use Config\Generators;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Commands\Modern\GeneratorFixtureCommand;
use Tests\Support\Commands\Modern\ImportSortingGeneratorCommand;
use Tests\Support\Commands\Modern\NoImportSortingGeneratorCommand;
use Tests\Support\Commands\Modern\TrimmedOptionsGeneratorCommand;
use Tests\Support\InvalidCommands\NoAttributeGeneratorCommand;

/**
 * @internal
 */
#[CoversClass(AbstractGeneratorCommand::class)]
#[Group('Others')]
final class AbstractGeneratorCommandTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    #[After]
    #[Before]
    protected function resetAll(): void
    {
        $this->resetServices();

        CLI::reset();

        $dir = APPPATH . 'Widgets';

        if (is_dir($dir)) {
            helper('filesystem');
            delete_files($dir, true, false, true);
            rmdir($dir);
        }
    }

    public function testConstructorSeedsAttributeState(): void
    {
        $command = new GeneratorFixtureCommand(new Commands());

        $this->assertSame('Widget', $this->getPrivateProperty($command, 'component'));
        $this->assertSame('Widgets', $this->getPrivateProperty($command, 'directory'));
        $this->assertSame('config.tpl.php', $this->getPrivateProperty($command, 'template'));
        $this->assertSame('CLI.generator.className.default', $this->getPrivateProperty($command, 'classNameLang'));
        $this->assertNull($this->getPrivateProperty($command, 'namespace'));
        $this->assertNull($this->getPrivateProperty($command, 'templatePath'));
        $this->assertTrue($this->getPrivateProperty($command, 'sortImports'));
    }

    public function testCommandRequiresGeneratorCommandAttribute(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessageMatches('/^Command class ".*" is missing the CodeIgniter\\\\CLI\\\\Attributes\\\\GeneratorCommand attribute\.$/');

        new NoAttributeGeneratorCommand(new Commands());
    }

    public function testCommandDeclaresGeneratorDefinition(): void
    {
        $command = new GeneratorFixtureCommand(new Commands());

        $arguments = $command->getArgumentsDefinition();

        $this->assertSame(['name'], array_keys($arguments));
        $this->assertTrue($arguments['name']->required);
        $this->assertSame(
            ['help', 'no-header', 'no-interaction', 'namespace', 'suffix', 'force'],
            array_keys($command->getOptionsDefinition()),
        );
        $this->assertSame(
            ['h' => 'help', 'N' => 'no-interaction', 'n' => 'namespace', 's' => 'suffix', 'f' => 'force'],
            $command->getShortcuts(),
        );
        $this->assertSame('make:testwidget [options] [--] <name>', $command->getUsages()[0]);
    }

    public function testTrimmedCommandDeclaresReducedDefinition(): void
    {
        $command = new TrimmedOptionsGeneratorCommand(new Commands());

        $this->assertSame(
            ['help', 'no-header', 'no-interaction', 'namespace'],
            array_keys($command->getOptionsDefinition()),
        );
    }

    public function testGenerateClassCreatesFile(): void
    {
        $command = new GeneratorFixtureCommand(new Commands());
        $command->setInteractive(false);

        $exitCode = $command->run(['foo'], []);

        $this->assertSame(EXIT_SUCCESS, $exitCode);
        $this->assertStringContainsString('File created: ', $this->getStreamFilterBuffer());

        $target = APPPATH . 'Widgets' . DIRECTORY_SEPARATOR . 'Foo.php';
        $this->assertFileExists($target);

        $content = file_get_contents($target);
        $this->assertIsString($content);
        $this->assertStringContainsString('namespace App\Widgets;', $content);
        $this->assertStringContainsString('class Foo extends BaseConfig', $content);
    }

    public function testGenerateViewCreatesFile(): void
    {
        $command = new GeneratorFixtureCommand(new Commands());
        $command->setInteractive(false);

        $this->assertSame(EXIT_SUCCESS, $command->run(['foo'], []));
        $this->assertSame(EXIT_SUCCESS, $this->getPrivateMethodInvoker($command, 'generateView')('App\Widgets\FooView'));

        $content = file_get_contents(APPPATH . 'Widgets' . DIRECTORY_SEPARATOR . 'FooView.php');
        $this->assertIsString($content);
        $this->assertStringContainsString('class FooView extends BaseConfig', $content);
    }

    public function testAlreadyQualifiedInputIsPreserved(): void
    {
        $command = new GeneratorFixtureCommand(new Commands());
        $command->setInteractive(false);

        $exitCode = $command->run(['App\Widgets\Foo'], []);

        $this->assertSame(EXIT_SUCCESS, $exitCode);
        $this->assertFileExists(APPPATH . 'Widgets' . DIRECTORY_SEPARATOR . 'Foo.php');
    }

    public function testInteriorRootNamespaceSegmentIsPreserved(): void
    {
        $command = new GeneratorFixtureCommand(new Commands());
        $command->setInteractive(false);

        $exitCode = $command->run(['Sub/App/Foo'], []);

        $this->assertSame(EXIT_SUCCESS, $exitCode);

        $target = APPPATH . 'Widgets' . DIRECTORY_SEPARATOR . 'Sub' . DIRECTORY_SEPARATOR . 'App' . DIRECTORY_SEPARATOR . 'Foo.php';
        $this->assertFileExists($target);
        $content = file_get_contents($target);
        $this->assertIsString($content);
        $this->assertStringContainsString('namespace App\Widgets\Sub\App;', $content);
    }

    public function testTrailingSeparatorInNameIsIgnored(): void
    {
        $command = new GeneratorFixtureCommand(new Commands());
        $command->setInteractive(false);

        $exitCode = $command->run(['foo/'], []);

        $this->assertSame(EXIT_SUCCESS, $exitCode);
        $content = file_get_contents(APPPATH . 'Widgets' . DIRECTORY_SEPARATOR . 'Foo.php');
        $this->assertIsString($content);
        $this->assertStringContainsString('class Foo extends BaseConfig', $content);
    }

    public function testUppercaseComponentSuffixIsCaseNormalized(): void
    {
        $command = new GeneratorFixtureCommand(new Commands());
        $command->setInteractive(false);

        $command->run(['fooWIDGET'], []);

        $this->assertFileExists(APPPATH . 'Widgets' . DIRECTORY_SEPARATOR . 'FooWidget.php');
    }

    public function testAttributeNamespaceOverridesNamespaceOption(): void
    {
        $command = new NoImportSortingGeneratorCommand(new Commands());
        $command->setInteractive(false);

        $exitCode = $command->run(['foo'], ['namespace' => 'Tests\Support']);

        $this->assertSame(EXIT_SUCCESS, $exitCode);
        $this->assertFileExists(APPPATH . 'Widgets' . DIRECTORY_SEPARATOR . 'Foo.php');
    }

    public function testGenerateClassRejectsExistingFileWithoutForce(): void
    {
        $command = new GeneratorFixtureCommand(new Commands());
        $command->setInteractive(false);

        $this->assertSame(EXIT_SUCCESS, $command->run(['foo'], []));

        $exitCode = $command->run(['foo'], []);

        $this->assertSame(EXIT_ERROR, $exitCode);
        $this->assertStringContainsString('File exists: ', $this->getStreamFilterBuffer());
    }

    public function testGenerateClassOverwritesWithForce(): void
    {
        $command = new GeneratorFixtureCommand(new Commands());
        $command->setInteractive(false);

        $this->assertSame(EXIT_SUCCESS, $command->run(['foo'], []));

        $exitCode = $command->run(['foo'], ['force' => null]);

        $this->assertSame(EXIT_SUCCESS, $exitCode);
        $this->assertStringContainsString('File overwritten: ', $this->getStreamFilterBuffer());
    }

    public function testSuffixOptionAppendsComponent(): void
    {
        $command = new GeneratorFixtureCommand(new Commands());
        $command->setInteractive(false);

        $command->run(['foo'], ['suffix' => null]);

        $this->assertFileExists(APPPATH . 'Widgets' . DIRECTORY_SEPARATOR . 'FooWidget.php');
    }

    public function testExistingComponentSuffixIsCaseNormalized(): void
    {
        $command = new GeneratorFixtureCommand(new Commands());
        $command->setInteractive(false);

        $command->run(['Foowidget'], []);

        $this->assertFileExists(APPPATH . 'Widgets' . DIRECTORY_SEPARATOR . 'FooWidget.php');
    }

    public function testForcedSuffixingWithoutSuffixOption(): void
    {
        $command = new TrimmedOptionsGeneratorCommand(new Commands());
        $command->setInteractive(false);

        $exitCode = $command->run(['foo'], []);

        $this->assertSame(EXIT_SUCCESS, $exitCode);
        $this->assertFileExists(APPPATH . 'Widgets' . DIRECTORY_SEPARATOR . 'FooWidget.php');
    }

    public function testCustomReplacementsTakePrecedenceOverCorePairs(): void
    {
        $command = new TrimmedOptionsGeneratorCommand(new Commands());
        $command->setInteractive(false);

        $command->run(['foo'], []);

        $content = file_get_contents(APPPATH . 'Widgets' . DIRECTORY_SEPARATOR . 'FooWidget.php');
        $this->assertIsString($content);
        $this->assertStringContainsString('namespace App\Widgets\Custom;', $content);
    }

    public function testDotSegmentsInNameAreRejected(): void
    {
        $routes = APPPATH . 'Config' . DIRECTORY_SEPARATOR . 'Routes.php';
        $before = file_get_contents($routes);

        $command = new GeneratorFixtureCommand(new Commands());
        $command->setInteractive(false);

        $exitCode = $command->run(['../Config/Routes'], ['force' => null]);

        $this->assertSame(EXIT_ERROR, $exitCode);
        $this->assertStringContainsString('Class name "App\\Widgets\\..\\Config\\Routes" is not valid.', $this->getStreamFilterBuffer());
        $this->assertSame($before, file_get_contents($routes));
    }

    public function testInvalidClassNameSegmentIsRejected(): void
    {
        $command = new GeneratorFixtureCommand(new Commands());
        $command->setInteractive(false);

        $exitCode = $command->run(['foo-bar'], []);

        $this->assertSame(EXIT_ERROR, $exitCode);
        $this->assertStringContainsString('Class name "App\\Widgets\\Foo-bar" is not valid.', $this->getStreamFilterBuffer());
        $this->assertDirectoryDoesNotExist(APPPATH . 'Widgets');
    }

    public function testAlreadySuffixedShortNamesAreNotDoubled(): void
    {
        $command = new GeneratorFixtureCommand(new Commands());
        $command->setInteractive(false);

        $command->run(['XWidget'], ['suffix' => null]);
        $command->run(['Widget'], ['suffix' => null]);

        $this->assertFileExists(APPPATH . 'Widgets' . DIRECTORY_SEPARATOR . 'XWidget.php');
        $this->assertFileExists(APPPATH . 'Widgets' . DIRECTORY_SEPARATOR . 'Widget.php');
    }

    public function testUndefinedNamespaceFails(): void
    {
        $command = new GeneratorFixtureCommand(new Commands());
        $command->setInteractive(false);

        $exitCode = $command->run(['foo'], ['namespace' => 'CodeIgnite']);

        $this->assertSame(EXIT_ERROR, $exitCode);
        $this->assertStringContainsString('Namespace "CodeIgnite" is not defined.', $this->getStreamFilterBuffer());
    }

    public function testInteractiveRunPromptsForClassName(): void
    {
        $io = new MockInputOutput();
        $io->setInputs(['foo']);
        CLI::setInputOutput($io);

        $command = new GeneratorFixtureCommand(new Commands());
        $command->setInteractive(true);

        $exitCode = $command->run([], []);

        $this->assertSame(EXIT_SUCCESS, $exitCode);
        $this->assertStringContainsString('Class name', $io->getOutput());
        $this->assertFileExists(APPPATH . 'Widgets' . DIRECTORY_SEPARATOR . 'Foo.php');
    }

    public function testInteractivePromptUsesClassNameLang(): void
    {
        $io = new MockInputOutput();
        $io->setInputs(['foo']);
        CLI::setInputOutput($io);

        $command = new TrimmedOptionsGeneratorCommand(new Commands());
        $command->setInteractive(true);

        $exitCode = $command->run([], []);

        $this->assertSame(EXIT_SUCCESS, $exitCode);
        $this->assertStringContainsString('Config class name', $io->getOutput());
        $this->assertFileExists(APPPATH . 'Widgets' . DIRECTORY_SEPARATOR . 'FooWidget.php');
    }

    public function testDecliningCodeIgniterNamespaceCancelsOperation(): void
    {
        $io = new MockInputOutput();
        $io->setInputs(['n']);
        CLI::setInputOutput($io);

        $command = new GeneratorFixtureCommand(new Commands());
        $command->setInteractive(true);

        $exitCode = $command->run(['foo'], ['namespace' => 'CodeIgniter']);

        $this->assertSame(EXIT_SUCCESS, $exitCode);
        $this->assertStringContainsString('Operation has been cancelled.', $io->getOutput());
        $this->assertFileDoesNotExist(SYSTEMPATH . 'Widgets' . DIRECTORY_SEPARATOR . 'Foo.php');
    }

    public function testAcceptingCodeIgniterNamespacePromptProceeds(): void
    {
        $io = new MockInputOutput();
        $io->setInputs(['y']);
        CLI::setInputOutput($io);

        $command = new GeneratorFixtureCommand(new Commands());
        $command->setInteractive(true);

        $exitCode = $command->run([CLI::class], ['namespace' => 'CodeIgniter']);

        $this->assertSame(EXIT_ERROR, $exitCode);
        $this->assertStringContainsString('File exists: ', $io->getOutput());
    }

    public function testImportsAreSortedInGeneratedContent(): void
    {
        $command = new ImportSortingGeneratorCommand(new Commands());
        $command->setInteractive(false);

        $command->run(['foo'], []);

        $content = file_get_contents(APPPATH . 'Widgets' . DIRECTORY_SEPARATOR . 'Foo.php');
        $this->assertIsString($content);
        $this->assertStringContainsString("use App\\Alpha;\nuse App\\Zebra;", $content);
    }

    public function testImportSortingCanBeDisabled(): void
    {
        $command = new NoImportSortingGeneratorCommand(new Commands());
        $command->setInteractive(false);

        $command->run(['foo'], []);

        $content = file_get_contents(APPPATH . 'Widgets' . DIRECTORY_SEPARATOR . 'Foo.php');
        $this->assertIsString($content);
        $this->assertStringContainsString("use App\\Zebra;\nuse App\\Alpha;", $content);
    }

    public function testRenderTemplateFallsBackWhenConfigEntryIsNotString(): void
    {
        config(Generators::class)->views['make:testwidget'] = [
            'class' => 'CodeIgniter\Commands\Generators\Views\config.tpl.php',
            'view'  => 'CodeIgniter\Commands\Generators\Views\cell_view.tpl.php',
        ];

        $command = new GeneratorFixtureCommand(new Commands());
        $command->setInteractive(false);

        $exitCode = $command->run(['foo'], []);

        $this->assertSame(EXIT_SUCCESS, $exitCode);
        $content = file_get_contents(APPPATH . 'Widgets' . DIRECTORY_SEPARATOR . 'Foo.php');
        $this->assertIsString($content);
        $this->assertStringContainsString('class Foo extends BaseConfig', $content);
    }

    public function testRenderTemplateFallsBackWhenConfiguredViewIsBroken(): void
    {
        config(Generators::class)->views['make:testwidget'] = 'App\Missing\widget.tpl.php';

        $command = new GeneratorFixtureCommand(new Commands());
        $command->setInteractive(false);

        $exitCode = $command->run(['foo'], []);

        $this->assertSame(EXIT_SUCCESS, $exitCode);
        $content = file_get_contents(APPPATH . 'Widgets' . DIRECTORY_SEPARATOR . 'Foo.php');
        $this->assertIsString($content);
        $this->assertStringContainsString('class Foo extends BaseConfig', $content);
    }

    public function testRenderTemplateUsesConfiguredView(): void
    {
        config(Generators::class)->views['make:testwidget'] = 'CodeIgniter\Commands\Generators\Views\seeder.tpl.php';

        $command = new GeneratorFixtureCommand(new Commands());
        $command->setInteractive(false);

        $exitCode = $command->run(['foo'], []);

        $this->assertSame(EXIT_SUCCESS, $exitCode);
        $content = file_get_contents(APPPATH . 'Widgets' . DIRECTORY_SEPARATOR . 'Foo.php');
        $this->assertIsString($content);
        $this->assertStringContainsString('class Foo extends Seeder', $content);
    }
}
