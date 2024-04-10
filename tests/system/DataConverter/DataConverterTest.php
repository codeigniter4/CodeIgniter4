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

namespace CodeIgniter\DataConverter;

use Closure;
use CodeIgniter\HTTP\URI;
use CodeIgniter\I18n\Time;
use CodeIgniter\Test\CIUnitTestCase;
use InvalidArgumentException;
use Tests\Support\Entity\CustomUser;
use Tests\Support\Entity\User;

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

        $data = $converter->fromDataSource($dbData);

        $this->assertSame($expected, $data);
    }

    /**
     * @dataProvider provideConvertDataToDB
     */
    public function testConvertDataToDB(array $types, array $phpData, array $expected): void
    {
        $converter = $this->createDataConverter($types);

        $data = $converter->toDataSource($phpData);

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
        $converter = $this->createDataConverter($types, [], db_connect());

        $dbData = [
            'id'   => '1',
            'date' => '2023-11-18 14:18:18',
        ];
        $data = $converter->fromDataSource($dbData);

        $this->assertInstanceOf(Time::class, $data['date']);
        $expectedDate = Time::parse('2023-11-18 14:18:18');
        $this->assertSame($expectedDate->getTimestamp(), $data['date']->getTimestamp());
    }

    public function testDateTimeConvertDataFromDBWithFormat(): void
    {
        $types = [
            'id'   => 'int',
            'date' => 'datetime[us]',
        ];
        $converter = $this->createDataConverter($types, [], db_connect());

        $dbData = [
            'id'   => '1',
            'date' => '2009-02-15 00:00:01.123456',
        ];
        $data = $converter->fromDataSource($dbData);

        $this->assertInstanceOf(Time::class, $data['date']);
        $expectedDate = Time::createFromFormat('Y-m-d H:i:s.u', '2009-02-15 00:00:01.123456');
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
        $data = $converter->toDataSource($phpData);

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
        $data = $converter->fromDataSource($dbData);

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
        $data = $converter->toDataSource($phpData);

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
        $data = $converter->fromDataSource($dbData);

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
        $data = $converter->toDataSource($phpData);

        $this->assertSame('http://example.com/', $data['url']);
    }

    public function testInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No such handler for "id". Invalid type: invalid');

        $types = [
            'id'     => 'invalid',
            'remark' => 'json-array',
        ];
        $converter = $this->createDataConverter($types);

        $dbData = [
            'id'     => '1',
            'remark' => '{"foo":"bar", "baz":true}',
        ];
        $converter->fromDataSource($dbData);
    }

    public function testInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            '[CodeIgniter\DataCaster\Cast\JsonCast] Invalid value type: bool, and its value: true'
        );

        $types = [
            'id'     => 'int',
            'remark' => 'json-array',
        ];
        $converter = $this->createDataConverter($types);

        $dbData = [
            'id'     => '1',
            'remark' => true,
        ];
        $converter->fromDataSource($dbData);
    }

    public function testInvalidCastHandler(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Invalid class type. It must implement CastInterface. class: CodeIgniter\DataConverter\DataConverter'
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
        $converter->fromDataSource($dbData);
    }

    public function testNotNullable(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Field "remark" is not nullable, but null was passed.');

        $types = [
            'id'     => 'int',
            'remark' => 'json-array',
        ];
        $converter = $this->createDataConverter($types);

        $dbData = [
            'id'     => 1,
            'remark' => null,
        ];
        $converter->toDataSource($dbData);
    }

    private function createDataConverter(
        array $types,
        array $handlers = [],
        ?object $helper = null,
        Closure|string|null $reconstructor = 'reconstruct',
        Closure|string|null $extractor = null
    ): DataConverter {
        return new DataConverter($types, $handlers, $helper, $reconstructor, $extractor);
    }

    public function testReconstructObjectWithReconstructMethod(): void
    {
        $types = [
            'id'         => 'int',
            'email'      => 'json-array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
        $converter = $this->createDataConverter($types, [], db_connect());

        $dbData = [
            'id'         => '1',
            'name'       => 'John Smith',
            'email'      => '["john@example.com"]',
            'country'    => 'US',
            'created_at' => '2023-12-02 07:35:57',
            'updated_at' => '2023-12-02 07:35:57',
        ];
        /** @var CustomUser $obj */
        $obj = $converter->reconstruct(CustomUser::class, $dbData);

        $this->assertIsInt($obj->id);
        $this->assertIsArray($obj->email);
        $this->assertInstanceOf(Time::class, $obj->created_at);
        $this->assertInstanceOf(Time::class, $obj->updated_at);
    }

    public function testReconstructObjectWithClosure(): void
    {
        $types = [
            'id'         => 'int',
            'email'      => 'json-array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
        $reconstructor = static function ($array) {
            $user = new User();
            $user->fill($array);

            return $user;
        };
        $converter = $this->createDataConverter($types, [], db_connect(), $reconstructor);

        $dbData = [
            'id'         => '1',
            'name'       => 'John Smith',
            'email'      => '["john@example.com"]',
            'country'    => 'US',
            'created_at' => '2023-12-02 07:35:57',
            'updated_at' => '2023-12-02 07:35:57',
        ];
        /** @var CustomUser $obj */
        $obj = $converter->reconstruct(CustomUser::class, $dbData);

        $this->assertIsInt($obj->id);
        $this->assertIsArray($obj->email);
        $this->assertInstanceOf(Time::class, $obj->created_at);
        $this->assertInstanceOf(Time::class, $obj->updated_at);
    }

    public function testExtract(): void
    {
        $types = [
            'id'         => 'int',
            'email'      => 'json-array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
        $converter = $this->createDataConverter($types);

        $phpData = [
            'id'         => 1,
            'name'       => 'John Smith',
            'email'      => ['john@example.com'],
            'country'    => 'US',
            'created_at' => Time::parse('2023-12-02 07:35:57'),
            'updated_at' => Time::parse('2023-12-02 07:35:57'),
        ];
        $obj = CustomUser::reconstruct($phpData);

        $array = $converter->extract($obj);

        $this->assertSame([
            'id'         => 1,
            'name'       => 'John Smith',
            'email'      => '["john@example.com"]',
            'country'    => 'US',
            'created_at' => '2023-12-02 07:35:57',
            'updated_at' => '2023-12-02 07:35:57',
        ], $array);
    }

    public function testExtractWithExtractMethod(): void
    {
        $types = [
            'id'         => 'int',
            'email'      => 'json-array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
        $converter = $this->createDataConverter($types, [], null, 'toRawArray');

        $phpData = [
            'id'         => 1,
            'name'       => 'John Smith',
            'email'      => ['john@example.com'],
            'country'    => 'US',
            'created_at' => Time::parse('2023-12-02 07:35:57'),
            'updated_at' => Time::parse('2023-12-02 07:35:57'),
        ];
        $obj = new User($phpData);

        $array = $converter->extract($obj);

        $this->assertSame([
            'country'    => 'US',
            'id'         => 1,
            'name'       => 'John Smith',
            'email'      => '["john@example.com"]',
            'created_at' => '2023-12-02 07:35:57',
            'updated_at' => '2023-12-02 07:35:57',
        ], $array);
    }

    public function testExtractWithClosure(): void
    {
        $types = [
            'id'         => 'int',
            'email'      => 'json-array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
        $extractor = static function ($obj) {
            $array['id']         = $obj->id;
            $array['name']       = $obj->name;
            $array['created_at'] = $obj->created_at;

            return $array;
        };
        $converter = $this->createDataConverter($types, [], db_connect(), null, $extractor);

        $phpData = [
            'id'         => 1,
            'name'       => 'John Smith',
            'email'      => ['john@example.com'],
            'country'    => 'US',
            'created_at' => Time::parse('2023-12-02 07:35:57'),
            'updated_at' => Time::parse('2023-12-02 07:35:57'),
        ];
        $obj = CustomUser::reconstruct($phpData);

        $array = $converter->extract($obj);

        $this->assertSame([
            'id'         => 1,
            'name'       => 'John Smith',
            'created_at' => '2023-12-02 07:35:57',
        ], $array);
    }
}
