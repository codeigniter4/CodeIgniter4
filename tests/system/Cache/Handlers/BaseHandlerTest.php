<?php

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
use stdClass;
use Tests\Support\Cache\RestrictiveHandler;

/**
 * @internal
 *
 * @group Others
 */
final class BaseHandlerTest extends CIUnitTestCase
{
    /**
     * @dataProvider provideValidateKeyInvalidType
     *
     * @param mixed $input
     */
    public function testValidateKeyInvalidType($input): void
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Cache key must be a string');

        BaseHandler::validateKey($input);
    }

    public static function provideValidateKeyInvalidType(): iterable
    {
        return [
            [true],
            [false],
            [null],
            [42],
            [new stdClass()],
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
