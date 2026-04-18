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

use CodeIgniter\CLI\Exceptions\InvalidArgumentDefinitionException;
use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[CoversClass(Argument::class)]
#[Group('Others')]
final class ArgumentTest extends CIUnitTestCase
{
    public function testBasicArgumentExposesProperties(): void
    {
        $argument = new Argument(
            name: 'path',
            description: 'The path to operate on.',
            required: true,
        );

        $this->assertSame('path', $argument->name);
        $this->assertSame('The path to operate on.', $argument->description);
        $this->assertTrue($argument->required);
        $this->assertFalse($argument->isArray);
        $this->assertNull($argument->default);
    }

    public function testArrayArgumentDefaultsToEmptyArrayWhenOmitted(): void
    {
        $argument = new Argument(name: 'tags', isArray: true);

        $this->assertTrue($argument->isArray);
        $this->assertFalse($argument->required);
        $this->assertSame([], $argument->default);
    }

    public function testArrayArgumentRetainsExplicitDefault(): void
    {
        $argument = new Argument(name: 'tags', isArray: true, default: ['a', 'b']);

        $this->assertSame(['a', 'b'], $argument->default);
    }

    public function testOptionalArgumentRetainsStringDefault(): void
    {
        $argument = new Argument(name: 'driver', default: 'file');

        $this->assertFalse($argument->required);
        $this->assertSame('file', $argument->default);
    }

    /**
     * @param array<string, mixed> $parameters
     */
    #[DataProvider('provideInvalidDefinitionsAreRejected')]
    public function testInvalidDefinitionsAreRejected(string $message, array $parameters): void
    {
        $this->expectException(InvalidArgumentDefinitionException::class);
        $this->expectExceptionMessage($message);

        new Argument(...$parameters);
    }

    /**
     * @return iterable<string, array{string, array<string, mixed>}>
     */
    public static function provideInvalidDefinitionsAreRejected(): iterable
    {
        yield 'empty name' => [
            'Argument name cannot be empty.',
            ['name' => ''],
        ];

        yield 'invalid name' => [
            'Argument name "invalid name" is not valid.',
            ['name' => 'invalid name'],
        ];

        yield 'reserved name' => [
            'Argument name "extra_arguments" is reserved and cannot be used.',
            ['name' => 'extra_arguments'],
        ];

        yield 'required array argument' => [
            'Array argument "test" cannot be required.',
            ['name' => 'test', 'required' => true, 'isArray' => true],
        ];

        yield 'required argument with default value' => [
            'Argument "test" is required and must not have a default value.',
            ['name' => 'test', 'required' => true, 'default' => 'value'],
        ];

        yield 'optional argument with null default value' => [
            'Argument "test" is optional and must have a default value.',
            ['name' => 'test'],
        ];

        yield 'array argument with non-array default value' => [
            'Array argument "test" must have an array default value or null.',
            ['name' => 'test', 'isArray' => true, 'default' => 'value'],
        ];

        yield 'non-array argument with array default value' => [
            'Argument "test" does not accept an array default value.',
            ['name' => 'test', 'default' => ['value']],
        ];
    }
}
