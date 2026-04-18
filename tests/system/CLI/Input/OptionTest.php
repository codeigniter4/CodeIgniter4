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

namespace CodeIgniter\CLI\Input;

use CodeIgniter\CLI\Exceptions\InvalidOptionDefinitionException;
use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[CoversClass(Option::class)]
#[Group('Others')]
final class OptionTest extends CIUnitTestCase
{
    public function testFlagOptionExposesDefaults(): void
    {
        $option = new Option(name: 'verbose', shortcut: 'v', description: 'Verbose output.');

        $this->assertSame('verbose', $option->name);
        $this->assertSame('v', $option->shortcut);
        $this->assertSame('Verbose output.', $option->description);
        $this->assertFalse($option->acceptsValue);
        $this->assertFalse($option->requiresValue);
        $this->assertFalse($option->isArray);
        $this->assertFalse($option->negatable);
        $this->assertNull($option->valueLabel);
        $this->assertNull($option->negation);
        $this->assertFalse($option->default);
    }

    public function testLeadingDoubleDashIsStrippedFromName(): void
    {
        $option = new Option(name: '--force');

        $this->assertSame('force', $option->name);
    }

    public function testLeadingDashIsStrippedFromShortcut(): void
    {
        $option = new Option(name: 'force', shortcut: '-f');

        $this->assertSame('f', $option->shortcut);
    }

    public function testRequiresValueImpliesAcceptsValue(): void
    {
        $option = new Option(name: 'path', requiresValue: true, default: '/tmp');

        $this->assertTrue($option->acceptsValue);
        $this->assertTrue($option->requiresValue);
        $this->assertSame('/tmp', $option->default);
    }

    public function testIsArrayImpliesAcceptsValue(): void
    {
        $option = new Option(name: 'tags', requiresValue: true, isArray: true, default: ['a']);

        $this->assertTrue($option->acceptsValue);
        $this->assertTrue($option->isArray);
        $this->assertSame(['a'], $option->default);
    }

    public function testValueLabelDefaultsToName(): void
    {
        $option = new Option(name: 'path', acceptsValue: true);

        $this->assertSame('path', $option->valueLabel);
    }

    public function testValueLabelCanBeCustomized(): void
    {
        $option = new Option(name: 'path', acceptsValue: true, valueLabel: 'file');

        $this->assertSame('file', $option->valueLabel);
    }

    public function testNegatableOptionComputesNegation(): void
    {
        $option = new Option(name: 'force', negatable: true, default: false);

        $this->assertTrue($option->negatable);
        $this->assertSame('no-force', $option->negation);
        $this->assertFalse($option->default);
    }

    public function testNegatableOptionAcceptsBooleanDefault(): void
    {
        $option = new Option(name: 'force', negatable: true, default: true);

        $this->assertTrue($option->default);
    }

    /**
     * @param array<string, mixed> $parameters
     */
    #[DataProvider('provideInvalidDefinitionsAreRejected')]
    public function testInvalidDefinitionsAreRejected(string $message, array $parameters): void
    {
        $this->expectException(InvalidOptionDefinitionException::class);
        $this->expectExceptionMessage($message);

        new Option(...$parameters);
    }

    /**
     * @return iterable<string, array{string, array<string, mixed>}>
     */
    public static function provideInvalidDefinitionsAreRejected(): iterable
    {
        yield 'empty name' => [
            'Option name cannot be empty.',
            ['name' => ''],
        ];

        yield 'double dash only' => [
            'Option name cannot be empty.',
            ['name' => '--'],
        ];

        yield 'single dash only' => [
            'Option name "---" is not valid.',
            ['name' => '-'],
        ];

        yield 'reserved name' => [
            'Option name "--extra_options" is reserved and cannot be used.',
            ['name' => 'extra_options'],
        ];

        yield 'empty shortcut name' => [
            'Shortcut name cannot be empty.',
            ['name' => 'test', 'shortcut' => ''],
        ];

        yield 'single dash only shortcut' => [
            'Shortcut name cannot be empty.',
            ['name' => 'test', 'shortcut' => '-'],
        ];

        yield 'invalid shortcut name' => [
            'Shortcut name "-:" is not valid.',
            ['name' => 'test', 'shortcut' => '-:'],
        ];

        yield 'shortcut name with more than one character' => [
            'Shortcut name "-ab" must be a single character.',
            ['name' => 'test', 'shortcut' => '-ab'],
        ];

        yield 'negatable option accepting value' => [
            'Negatable option "--test" cannot be defined to accept a value.',
            ['name' => 'test', 'acceptsValue' => true, 'negatable' => true],
        ];

        yield 'array option not requiring value' => [
            'Array option "--test" must require a value.',
            ['name' => 'test', 'isArray' => true],
        ];

        yield 'option requiring value but has no default value' => [
            'Option "--test" requires a string default value.',
            ['name' => 'test', 'requiresValue' => true],
        ];

        yield 'negatable option cannot be array' => [
            'Negatable option "--test" cannot be defined as an array.',
            ['name' => 'test', 'isArray' => true, 'negatable' => true],
        ];

        yield 'option not accepting value but has default value' => [
            'Option "--test" does not accept a value and cannot have a default value.',
            ['name' => 'test', 'default' => 'value'],
        ];

        yield 'negatable option with non-boolean default value' => [
            'Negatable option "--test" must have a boolean default value.',
            ['name' => 'test', 'negatable' => true, 'default' => 'value'],
        ];

        yield 'negatable option with no default value' => [
            'Negatable option "--test" must have a boolean default value.',
            ['name' => 'test', 'negatable' => true],
        ];

        yield 'array option with non-array default value' => [
            'Array option "--test" must have an array default value or null.',
            ['name' => 'test', 'requiresValue' => true, 'isArray' => true, 'default' => 'value'],
        ];

        yield 'array option with empty array default value' => [
            'Array option "--test" cannot have an empty array as the default value.',
            ['name' => 'test', 'requiresValue' => true, 'isArray' => true, 'default' => []],
        ];
    }
}
