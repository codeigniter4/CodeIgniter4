<?php

namespace CodeIgniter\Cache\Handlers;

use CodeIgniter\Test\CIUnitTestCase;
use stdClass;
use Tests\Support\Cache\RestrictiveHandler;

/**
 * @internal
 */
final class BaseHandlerTest extends CIUnitTestCase
{
    /**
     * @dataProvider invalidTypeProvider
     */
    public function testValidateKeyInvalidType($input)
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Cache key must be a string');

        BaseHandler::validateKey($input);
    }

    public function invalidTypeProvider(): array
    {
        return [
            [true],
            [false],
            [null],
            [42],
            [new stdClass()],
        ];
    }

    public function testValidateKeySuccess()
    {
        $string = 'banana';
        $result = BaseHandler::validateKey($string);

        $this->assertSame($string, $result);
    }

    public function testValidateKeySuccessWithPrefix()
    {
        $string = 'banana';
        $result = BaseHandler::validateKey($string, 'prefix');

        $this->assertSame('prefix' . $string, $result);
    }

    public function testValidateExcessiveLength()
    {
        $string   = 'MoreThanTenCharacters';
        $expected = md5($string);

        $result = RestrictiveHandler::validateKey($string);

        $this->assertSame($expected, $result);
    }

    public function testValidateExcessiveLengthWithPrefix()
    {
        $string   = 'MoreThanTenCharacters';
        $expected = 'prefix' . md5($string);

        $result = RestrictiveHandler::validateKey($string, 'prefix');

        $this->assertSame($expected, $result);
    }
}
