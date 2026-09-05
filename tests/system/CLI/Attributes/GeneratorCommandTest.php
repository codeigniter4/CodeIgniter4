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

namespace CodeIgniter\CLI\Attributes;

use CodeIgniter\Exceptions\LogicException;
use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[CoversClass(GeneratorCommand::class)]
#[Group('Others')]
final class GeneratorCommandTest extends CIUnitTestCase
{
    public function testAttributeExposesProperties(): void
    {
        $generatorCommand = new GeneratorCommand(
            component: 'Model',
            template: 'model.tpl.php',
            directory: 'Models',
            classNameLang: 'CLI.generator.className.model',
            namespace: 'App',
            sortImports: false,
        );

        $this->assertSame('Model', $generatorCommand->component);
        $this->assertSame('model.tpl.php', $generatorCommand->template);
        $this->assertSame('Models', $generatorCommand->directory);
        $this->assertSame('CLI.generator.className.model', $generatorCommand->classNameLang);
        $this->assertSame('App', $generatorCommand->namespace);
        $this->assertFalse($generatorCommand->sortImports);
    }

    public function testAttributeProvidesDefaults(): void
    {
        $generatorCommand = new GeneratorCommand(component: 'Model', template: 'model.tpl.php');

        $this->assertNull($generatorCommand->directory);
        $this->assertSame('CLI.generator.className.default', $generatorCommand->classNameLang);
        $this->assertNull($generatorCommand->namespace);
        $this->assertTrue($generatorCommand->sortImports);
    }

    /**
     * @param array<string, mixed> $parameters
     */
    #[DataProvider('provideInvalidDefinitionsAreRejected')]
    public function testInvalidDefinitionsAreRejected(string $message, array $parameters): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage($message);

        new GeneratorCommand(...$parameters);
    }

    /**
     * @return iterable<string, array{string, array<string, mixed>}>
     */
    public static function provideInvalidDefinitionsAreRejected(): iterable
    {
        yield 'empty component' => [
            'Generator component cannot be empty.',
            ['component' => '', 'template' => 'model.tpl.php'],
        ];

        yield 'component with regex metacharacter' => [
            'Generator component "(" is not valid.',
            ['component' => '(', 'template' => 'model.tpl.php'],
        ];

        yield 'component with space' => [
            'Generator component "Widget Factory" is not valid.',
            ['component' => 'Widget Factory', 'template' => 'model.tpl.php'],
        ];

        yield 'empty template' => [
            'Generator template cannot be empty.',
            ['component' => 'Model', 'template' => ''],
        ];

        yield 'empty directory' => [
            'Generator directory cannot be empty.',
            ['component' => 'Model', 'template' => 'model.tpl.php', 'directory' => ''],
        ];

        yield 'empty namespace' => [
            'Generator namespace cannot be empty.',
            ['component' => 'Model', 'template' => 'model.tpl.php', 'namespace' => ''],
        ];

        yield 'directory with dot segment' => [
            'Generator directory "..\\Models" is not valid.',
            ['component' => 'Model', 'template' => 'model.tpl.php', 'directory' => '..\\Models'],
        ];

        yield 'namespace with slash separator' => [
            'Generator namespace "App/Models" is not valid.',
            ['component' => 'Model', 'template' => 'model.tpl.php', 'namespace' => 'App/Models'],
        ];
    }
}
