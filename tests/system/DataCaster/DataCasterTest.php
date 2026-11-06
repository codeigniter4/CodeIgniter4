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

namespace CodeIgniter\DataCaster;

use CodeIgniter\Entity\Exceptions\CastException;
use CodeIgniter\Exceptions\InvalidArgumentException;
use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\Group;
use stdClass;

/**
 * @internal
 */
#[Group('Others')]
final class DataCasterTest extends CIUnitTestCase
{
    public function testCastInvalidMethodException(): void
    {
        $this->expectException(CastException::class);
        $this->expectExceptionMessage('The "add" is invalid cast method, valid methods are: ["get", "set"].');

        $dataCaster = new DataCaster();
        $dataCaster->castAs([], 'name', 'add');
    }

    public function testCastAsWithUndefinedFieldReturnsOriginalValue(): void
    {
        $dataCaster = new DataCaster();
        $value      = 'unregistered_field_value';

        $this->assertSame($value, $dataCaster->castAs($value, 'undefined_field'));
    }

    public function testCastAsCachesCompiledTypesAcrossRepeatedCalls(): void
    {
        $types = [
            'id'       => 'int',
            'is_admin' => 'bool',
            'tags'     => 'csv',
            'options'  => 'json[array]',
            'bio'      => '?string',
        ];

        $dataCaster = new DataCaster(null, $types);

        // First call compiles rules
        $this->assertSame(42, $dataCaster->castAs('42', 'id'));
        $this->assertTrue($dataCaster->castAs('1', 'is_admin'));
        $this->assertSame(['news', 'tech'], $dataCaster->castAs('news,tech', 'tags'));
        $this->assertSame(['theme' => 'dark'], $dataCaster->castAs('{"theme":"dark"}', 'options'));

        // Subsequent calls use compiled cache
        $this->assertSame(100, $dataCaster->castAs('100', 'id'));
        $this->assertFalse($dataCaster->castAs('0', 'is_admin'));
        $this->assertSame(['php'], $dataCaster->castAs('php', 'tags'));
        $this->assertSame(['lang' => 'en'], $dataCaster->castAs('{"lang":"en"}', 'options'));
    }

    public function testCompiledTypeCacheIsSharedByType(): void
    {
        $dataCaster = new DataCaster(null, [
            'first'  => 'int',
            'second' => 'int',
            'third'  => 'bool',
        ]);

        $dataCaster->castAs('1', 'first');
        $dataCaster->castAs('2', 'second');
        $dataCaster->castAs('1', 'third');

        $compiledTypes = $this->getPrivateProperty($dataCaster, 'compiledTypes');

        $this->assertIsArray($compiledTypes);
        $this->assertCount(2, $compiledTypes);
    }

    public function testCastAsNullableHandling(): void
    {
        $types = [
            'nullable_field' => '?int',
            'strict_field'   => 'int',
        ];

        $dataCaster = new DataCaster(null, $types, null, true);

        $this->assertNull($dataCaster->castAs(null, 'nullable_field'));
        $this->assertSame(123, $dataCaster->castAs('123', 'nullable_field'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Field "strict_field" is not nullable, but null was passed.');
        $dataCaster->castAs(null, 'strict_field');
    }

    public function testCastAsNullableWithUnknownTypeReturnsNull(): void
    {
        $dataCaster = new DataCaster(null, ['ghost' => '?unknown_handler']);

        // Null value for nullable field returns null even if handler is not defined
        $this->assertNull($dataCaster->castAs(null, 'ghost'));

        // Non-null value triggers missing handler exception
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No such handler for "ghost". Invalid type: unknown_handler');
        $dataCaster->castAs('foo', 'ghost');
    }

    public function testCastAsNotNullableStrictThrowsBeforeCheckingHandler(): void
    {
        $dataCaster = new DataCaster(null, ['ghost' => 'unknown_handler'], null, true);

        // Strict mode throws not nullable exception first
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Field "ghost" is not nullable, but null was passed.');
        $dataCaster->castAs(null, 'ghost');
    }

    public function testSetTypesInvalidatesCompiledCache(): void
    {
        $dataCaster = new DataCaster(null, ['data' => 'int']);

        $this->assertSame(5, $dataCaster->castAs('5', 'data'));
        $this->assertArrayHasKey('int', $this->getPrivateProperty($dataCaster, 'compiledTypes'));

        // Change type to csv
        $dataCaster->setTypes(['data' => 'csv']);
        $this->assertSame([], $this->getPrivateProperty($dataCaster, 'compiledTypes'));
        $this->assertSame(['5'], $dataCaster->castAs('5', 'data'));

        $compiledTypes = $this->getPrivateProperty($dataCaster, 'compiledTypes');
        $this->assertArrayHasKey('csv', $compiledTypes);

        // Calling setTypes with identical types preserves cached compilation
        $dataCaster->setTypes(['data' => 'csv']);
        $this->assertSame($compiledTypes, $this->getPrivateProperty($dataCaster, 'compiledTypes'));
        $this->assertSame(['a', 'b'], $dataCaster->castAs('a,b', 'data'));
    }

    public function testCastAsNoSuchHandlerException(): void
    {
        $dataCaster = new DataCaster(null, ['name' => 'non_existent_type']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No such handler for "name". Invalid type: non_existent_type');

        $dataCaster->castAs('value', 'name');
    }

    public function testCastAsInvalidInterfaceExceptionWhenNotStrict(): void
    {
        /** @phpstan-var mixed $castHandlers */
        $castHandlers = [
            'invalid' => stdClass::class,
        ];

        $dataCaster = new DataCaster($castHandlers, ['field' => 'invalid'], null, false);

        $this->expectException(CastException::class);
        $this->expectExceptionMessage(CastException::forInvalidInterface(stdClass::class)->getMessage());

        $dataCaster->castAs('value', 'field');
    }
}
