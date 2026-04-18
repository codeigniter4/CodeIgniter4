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
#[CoversClass(Command::class)]
#[Group('Others')]
final class CommandTest extends CIUnitTestCase
{
    public function testAttributeExposesProperties(): void
    {
        $command = new Command(name: 'app:about', description: 'Displays basic info.', group: 'App');

        $this->assertSame('app:about', $command->name);
        $this->assertSame('Displays basic info.', $command->description);
        $this->assertSame('App', $command->group);
    }

    public function testAttributeAllowsOmittedDescriptionAndGroup(): void
    {
        $command = new Command(name: 'app:about');

        $this->assertSame('', $command->description);
        $this->assertSame('', $command->group);
    }

    /**
     * @param array<string, mixed> $parameters
     */
    #[DataProvider('provideInvalidDefinitionsAreRejected')]
    public function testInvalidDefinitionsAreRejected(string $message, array $parameters): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage($message);

        new Command(...$parameters);
    }

    /**
     * @return iterable<string, array{string, array<string, mixed>}>
     */
    public static function provideInvalidDefinitionsAreRejected(): iterable
    {
        yield 'empty name' => [
            'Command name cannot be empty.',
            ['name' => ''],
        ];

        yield 'name with whitespace' => [
            'Command name "invalid name" is not valid.',
            ['name' => 'invalid name'],
        ];

        yield 'name starting with colon' => [
            'Command name ":invalid" is not valid.',
            ['name' => ':invalid'],
        ];

        yield 'name ending with colon' => [
            'Command name "invalid:" is not valid.',
            ['name' => 'invalid:'],
        ];

        yield 'name with consecutive colons' => [
            'Command name "app::about" is not valid.',
            ['name' => 'app::about'],
        ];
    }
}
