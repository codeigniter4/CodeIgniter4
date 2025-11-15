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

namespace CodeIgniter\Cache\Handlers;

use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use stdClass;
use Tests\Support\Cache\RestrictiveHandler;

/**
 * @internal
 */
#[Group('Others')]
final class BaseHandlerTest extends CIUnitTestCase
{
    #[DataProvider('provideValidateKeyInvalidType')]
    public function testValidateKeyInvalidType(mixed $input): void
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Cache key must be a string');

        BaseHandler::validateKey($input);
    }

    /**
     * @return iterable<string, array{0: mixed}>
     */
    public static function provideValidateKeyInvalidType(): iterable
    {
        yield from [
            'true'   => [true],
            'false'  => [false],
            'null'   => [null],
            'int'    => [42],
            'object' => [new stdClass()],
        ];
    }

    public function testValidateKeyUsesConfig(): void
    {
        config('Cache')->reservedCharacters = 'b';

        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Cache key contains reserved characters b');

        BaseHandler::validateKey('banana');
    }

    public function testValidateKeySuccess(): void
    {
        $string = 'banana';
        $result = BaseHandler::validateKey($string);

        $this->assertSame($string, $result);
    }

    public function testValidateKeySuccessWithPrefix(): void
    {
        $string = 'banana';
        $result = BaseHandler::validateKey($string, 'prefix');

        $this->assertSame('prefix' . $string, $result);
    }

    public function testValidateExcessiveLength(): void
    {
        $string   = 'MoreThanTenCharacters';
        $expected = md5($string);

        $result = RestrictiveHandler::validateKey($string);

        $this->assertSame($expected, $result);
    }

    public function testValidateExcessiveLengthWithPrefix(): void
    {
        $string   = 'MoreThanTenCharacters';
        $expected = 'prefix' . md5($string);

        $result = RestrictiveHandler::validateKey($string, 'prefix');

        $this->assertSame($expected, $result);
    }
}
