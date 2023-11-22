<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\DataConverter;

use CodeIgniter\HTTP\URI;
use CodeIgniter\I18n\Time;
use CodeIgniter\Test\CIUnitTestCase;
use InvalidArgumentException;

/**
 * @internal
 *
 * @group Others
 */
final class DataConverterTest extends CIUnitTestCase
{
    public function testInstantiate(): void
    {
        $types = [
            'id'     => 'int',
            'remark' => 'json',
        ];
        $converter = $this->createDataConverter($types);

        $this->assertInstanceOf(DataConverter::class, $converter);
    }

    /**
     * @dataProvider provideConvertDataFromDB
     */
    public function testConvertDataFromDB(array $types, array $dbData, array $expected): void
    {
        $converter = $this->createDataConverter($types);

        $data = $converter->fromDatabase($dbData);

        $this->assertSame($expected, $data);
    }

    /**
     * @dataProvider provideConvertDataToDB
     */
    public function testConvertDataToDB(array $types, array $phpData, array $expected): void
    {
        $converter = $this->createDataConverter($types);

        $data = $converter->toDatabase($phpData);

        $this->assertSame($expected, $data);
    }

    public static function provideConvertDataFromDB(): iterable
    {
        yield from [
            'int and json-array' => [
                [
                    'id'     => 'int',
                    'remark' => 'json-array',
                ],
                [
                    'id'     => '1',
                    'remark' => '{"foo":"bar","baz":true}',
                ],
                [
                    'id'     => 1,
                    'remark' => ['foo' => 'bar', 'baz' => true],
                ],
            ],
            'nullable null' => [
                [
                    'id'     => 'int',
                    'remark' => '?json-array',
                ],
                [
                    'id'     => '1',
                    'remark' => null,
                ],
                [
                    'id'     => 1,
                    'remark' => null,
                ],
            ],
            'nullable not null' => [
                [
                    'id'     => 'int',
                    'remark' => '?json-array',
                ],
                [
                    'id'     => '1',
                    'remark' => '{"foo":"bar", "baz":true}',
                ],
                [
                    'id'     => 1,
                    'remark' => ['foo' => 'bar', 'baz' => true],
                ],
            ],
            'int-bool' => [
                [
                    'id'     => 'int',
                    'active' => 'int-bool',
                ],
                [
                    'id'     => '1',
                    'active' => '1',
                ],
                [
                    'id'     => 1,
                    'active' => true,
                ],
            ],
            'array' => [
                [
                    'id'   => 'int',
                    'attr' => 'array',
                ],
                [
                    'id'   => '1',
                    'attr' => 'a:1:{s:3:"foo";s:3:"bar";}',
                ],
                [
                    'id'   => 1,
                    'attr' => ['foo' => 'bar'],
                ],
            ],
            'bool 1' => [
                [
                    'id'     => 'int',
                    'active' => 'bool',
                ],
                [
                    'id'     => '1',
                    'active' => '1',
                ],
                [
                    'id'     => 1,
                    'active' => true,
                ],
            ],
            'bool t' => [
                [
                    'id'     => 'int',
                    'active' => 'bool',
                ],
                [
                    'id'     => '1',
                    'active' => 't',
                ],
                [
                    'id'     => 1,
                    'active' => true,
                ],
            ],
            'bool f' => [
                [
                    'id'     => 'int',
                    'active' => 'bool',
                ],
                [
                    'id'     => '1',
                    'active' => 'f',
                ],
                [
                    'id'     => 1,
                    'active' => false,
                ],
            ],
            'csv' => [
                [
                    'id'   => 'int',
                    'data' => 'csv',
                ],
                [
                    'id'   => '1',
                    'data' => 'foo,bar,bam',
                ],
                [
                    'id'   => 1,
                    'data' => ['foo', 'bar', 'bam'],
                ],
            ],
            'float' => [
                [
                    'id'   => 'int',
                    'temp' => 'float',
                ],
                [
                    'id'   => '1',
                    'temp' => '15.9',
                ],
                [
                    'id'   => 1,
                    'temp' => 15.9,
                ],
            ],
        ];
    }

    public static function provideConvertDataToDB(): iterable
    {
        yield from [
            'int and json-array' => [
                [
                    'id'     => 'int',
                    'remark' => 'json-array',
                ],
                [
                    'id'     => 1,
                    'remark' => ['foo' => 'bar', 'baz' => true],
                ],
                [
                    'id'     => 1,
                    'remark' => '{"foo":"bar","baz":true}',
                ],
            ],
            'nullable null' => [
                [
                    'id'     => 'int',
                    'remark' => '?json-array',
                ],
                [
                    'id'     => 1,
                    'remark' => null,
                ],
                [
                    'id'     => 1,
                    'remark' => null,
                ],
            ],
            'nullable not null' => [
                [
                    'id'     => 'int',
                    'remark' => '?json-array',
                ],
                [
                    'id'     => 1,
                    'remark' => ['foo' => 'bar', 'baz' => true],
                ],
                [
                    'id'     => 1,
                    'remark' => '{"foo":"bar","baz":true}',
                ],
            ],
            'int-bool' => [
                [
                    'id'     => 'int',
                    'active' => 'int-bool',
                ],
                [
                    'id'     => 1,
                    'active' => true,
                ],
                [
                    'id'     => 1,
                    'active' => 1,
                ],
            ],
            'array' => [
                [
                    'id'   => 'int',
                    'attr' => 'array',
                ],
                [
                    'id'   => 1,
                    'attr' => ['foo' => 'bar'],
                ],
                [
                    'id'   => 1,
                    'attr' => 'a:1:{s:3:"foo";s:3:"bar";}',
                ],
            ],
            'bool 1' => [
                [
                    'id'     => 'int',
                    'active' => 'bool',
                ],
                [
                    'id'     => 1,
                    'active' => true,
                ],
                [
                    'id'     => 1,
                    'active' => true,
                ],
            ],
            'csv' => [
                [
                    'id'   => 'int',
                    'data' => 'csv',
                ],
                [
                    'id'   => 1,
                    'data' => ['foo', 'bar', 'bam'],
                ],
                [
                    'id'   => 1,
                    'data' => 'foo,bar,bam',
                ],
            ],
            'float' => [
                [
                    'id'   => 'int',
                    'temp' => 'float',
                ],
                [
                    'id'   => 1,
                    'temp' => 15.9,
                ],
                [
                    'id'   => 1,
                    'temp' => 15.9,
                ],
            ],
        ];
    }

    public function testDateTimeConvertDataFromDB(): void
    {
        $types = [
            'id'   => 'int',
            'date' => 'datetime',
        ];
        $converter = $this->createDataConverter($types);

        $dbData = [
            'id'   => '1',
            'date' => '2023-11-18 14:18:18',
        ];
        $data = $converter->fromDatabase($dbData);

        $this->assertInstanceOf(Time::class, $data['date']);
        $expectedDate = Time::parse('2023-11-18 14:18:18');
        $this->assertSame($expectedDate->getTimestamp(), $data['date']->getTimestamp());
    }

    public function testDateTimeConvertDataToDB(): void
    {
        $types = [
            'id'   => 'int',
            'date' => 'datetime',
        ];
        $converter = $this->createDataConverter($types);

        $phpData = [
            'id'   => '1',
            'date' => Time::parse('2023-11-18 14:18:18'),
        ];
        $data = $converter->toDatabase($phpData);

        $this->assertSame('2023-11-18 14:18:18', $data['date']);
    }

    public function testTimestampConvertDataFromDB(): void
    {
        $types = [
            'id'   => 'int',
            'date' => 'timestamp',
        ];
        $converter = $this->createDataConverter($types);

        $dbData = [
            'id'   => '1',
            'date' => '1700285831',
        ];
        $data = $converter->fromDatabase($dbData);

        $this->assertInstanceOf(Time::class, $data['date']);
        $this->assertSame(1_700_285_831, $data['date']->getTimestamp());
    }

    public function testTimestampConvertDataToDB(): void
    {
        $types = [
            'id'   => 'int',
            'date' => 'timestamp',
        ];
        $converter = $this->createDataConverter($types);

        $phpData = [
            'id'   => '1',
            'date' => Time::createFromTimestamp(1_700_285_831),
        ];
        $data = $converter->toDatabase($phpData);

        $this->assertSame(1_700_285_831, $data['date']);
    }

    public function testURIConvertDataFromDB(): void
    {
        $types = [
            'id'  => 'int',
            'url' => 'uri',
        ];
        $converter = $this->createDataConverter($types);

        $dbData = [
            'id'  => '1',
            'url' => 'http://example.com/',
        ];
        $data = $converter->fromDatabase($dbData);

        $this->assertInstanceOf(URI::class, $data['url']);
        $this->assertSame('http://example.com/', (string) $data['url']);
    }

    public function testURIConvertDataToDB(): void
    {
        $types = [
            'id'  => 'int',
            'url' => 'uri',
        ];
        $converter = $this->createDataConverter($types);

        $phpData = [
            'id'  => '1',
            'url' => new URI('http://example.com/'),
        ];
        $data = $converter->toDatabase($phpData);

        $this->assertSame('http://example.com/', $data['url']);
    }

    public function testInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No such handler. Invalid type: invalid');

        $types = [
            'id'     => 'invalid',
            'remark' => 'json-array',
        ];
        $converter = $this->createDataConverter($types);

        $dbData = [
            'id'     => '1',
            'remark' => '{"foo":"bar", "baz":true}',
        ];
        $converter->fromDatabase($dbData);
    }

    public function testInvalidCastHandler(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Invalid class type. It must implement CastInterface. class: CodeIgniter\Database\DataConverter\DataConverter'
        );

        $types = [
            'id'     => 'int',
            'remark' => 'json-array',
        ];
        $converter = $this->createDataConverter($types, ['int' => DataConverter::class]);

        $dbData = [
            'id'     => '1',
            'remark' => '{"foo":"bar", "baz":true}',
        ];
        $converter->fromDatabase($dbData);
    }

    private function createDataConverter(array $types, array $handlers = []): DataConverter
    {
        return new DataConverter($types, $handlers);
    }
}
