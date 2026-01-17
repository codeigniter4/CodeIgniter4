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

namespace CodeIgniter\Entity;

use ArrayIterator;
use ArrayObject;
use Closure;
use CodeIgniter\DataCaster\DataCaster;
use CodeIgniter\Entity\Exceptions\CastException;
use CodeIgniter\HTTP\URI;
use CodeIgniter\I18n\Time;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ReflectionHelper;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use JsonSerializable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use ReflectionException;
use stdClass;
use Tests\Support\Entity\Cast\CastBase64;
use Tests\Support\Entity\Cast\CastPassParameters;
use Tests\Support\Entity\Cast\NotExtendsBaseCast;
use Tests\Support\Enum\ColorEnum;
use Tests\Support\Enum\RoleEnum;
use Tests\Support\Enum\StatusEnum;
use Tests\Support\SomeEntity;

/**
 * @internal
 */
#[Group('Others')]
final class EntityTest extends CIUnitTestCase
{
    use ReflectionHelper;

    public function testSetStringToPropertyNamedAttributes(): void
    {
        $entity = $this->getEntity();

        $entity->attributes = 'attributes';

        $this->assertSame('attributes', $entity->attributes);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/5762
     */
    public function testSetArrayToPropertyNamedAttributes(): void
    {
        $entity = new Entity();

        $entity->a          = 1;
        $entity->attributes = [1, 2, 3];

        $expected = [
            'a'          => 1,
            'attributes' => [
                0 => 1,
                1 => 2,
                2 => 3,
            ],
        ];
        $this->assertSame($expected, $entity->toRawArray());
    }

    public function testSetGetAttributesMethod(): void
    {
        $entity = new class () extends Entity {
            protected $attributes = [
                'foo'        => null,
                'attributes' => null,
            ];

            public function setAttributes(string $value): self
            {
                $this->attributes['attributes'] = $value;

                return $this;
            }

            public function getAttributes(): string
            {
                return $this->attributes['attributes'];
            }
        };

        $entity->setAttributes('attributes');

        $this->assertSame('attributes', $entity->getAttributes());
    }

    public function testSimpleSetAndGet(): void
    {
        $entity = $this->getEntity();

        $entity->foo = 'to wong';

        $this->assertSame('to wong', $entity->foo);
    }

    public function testGetterSetters(): void
    {
        $entity = $this->getEntity();

        $entity->bar = 'thanks';

        $this->assertSame('bar:thanks:bar', $entity->bar);
    }

    public function testNewGetterSetters(): void
    {
        $entity = $this->getNewSetterGetterEntity();

        $entity->bar = 'thanks';

        $this->assertSame('bar:thanks:bar', $entity->bar);

        $entity->setBar('BAR');

        $this->assertSame('BAR', $entity->getBar());
    }

    public function testUnsetUnsetsAttribute(): void
    {
        $entity = $this->getEntity();

        $this->assertSame('sumfin', $entity->default);

        $entity->default = 'else';

        $this->assertSame('else', $entity->default);

        unset($entity->default);

        $this->assertNull($entity->default);
    }

    public function testIssetWorksLikeTraditionalIsset(): void
    {
        $entity = $this->getEntity();

        $issetReturn = isset($entity->foo);

        $this->assertFalse($issetReturn);

        $attributes = $this->getPrivateProperty($entity, 'attributes');

        $issetFooReturn     = isset($attributes['foo']);
        $issetDefaultReturn = isset($attributes['default']);

        $this->assertFalse($issetFooReturn);
        $this->assertTrue($issetDefaultReturn);
    }

    public function testFill(): void
    {
        $entity = $this->getEntity();

        $entity->fill([
            'foo' => 123,
            'bar' => 234,
            'baz' => 4556,
        ]);

        $this->assertSame(123, $entity->foo);
        $this->assertSame('bar:234:bar', $entity->bar);
        $this->assertSame(4556, $entity->baz);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1567
     */
    public function testFillMapsEntities(): void
    {
        $entity = $this->getMappedEntity();

        $data = [
            'bar'  => 'foo',
            'orig' => 'simple',
        ];
        $entity->fill($data);

        $this->assertSame('foo', $entity->bar);
        $this->assertSame('oo:simple:oo', $entity->orig);
    }

    public function testDataMappingConvertsOriginalName(): void
    {
        $entity = $this->getMappedEntity();

        $entity->bar = 'made it';

        // Check db column name
        $this->assertSame('made it', $entity->foo);

        // Should also get from property name
        // since Model's would be looking for the property name
        $this->assertSame('made it', $entity->bar);

        // But it shouldn't actually set a class property for the column name...
        $this->expectException(ReflectionException::class);
        $this->getPrivateProperty($entity, 'bar');
    }

    public function testDataMappingWorksWithCustomSettersAndGetters(): void
    {
        $entity = $this->getMappedEntity();

        // Will map to "simple" column
        $entity->orig = 'first';

        $this->assertSame('oo:first:oo', $entity->simple);

        $entity->simple = 'second';

        $this->assertSame('oo:second:oo', $entity->simple);
    }

    public function testDataMappingIsset(): void
    {
        $entity = $this->getMappedEntity();

        // maps to 'foo' column
        $entity->bar = 'here';

        $isset = isset($entity->bar);
        $this->assertTrue($isset);

        $isset = isset($entity->foo);
        $this->assertFalse($isset);
    }

    public function testUnsetWorksWithMapping(): void
    {
        $entity = $this->getMappedEntity();

        // maps to 'foo' column
        $entity->bar = 'here';

        // doesn't work on db column name
        unset($entity->foo);

        $this->assertSame('here', $entity->bar);
        $this->assertSame('here', $entity->foo);

        // does work on property name
        unset($entity->bar);

        $isset = isset($entity->foo);
        $this->assertFalse($isset);
        $isset = isset($entity->bar);
        $this->assertFalse($isset);
    }

    public function testDateMutationFromString(): void
    {
        $entity     = $this->getEntity();
        $attributes = ['created_at' => '2017-07-15 13:23:34'];
        $this->setPrivateProperty($entity, 'attributes', $attributes);

        $time = $entity->created_at;

        $this->assertInstanceOf(Time::class, $time);
        $this->assertSame('2017-07-15 13:23:34', $time->format('Y-m-d H:i:s'));
    }

    public function testDateMutationFromTimestamp(): void
    {
        $stamp      = time();
        $entity     = $this->getEntity();
        $attributes = ['created_at' => $stamp];
        $this->setPrivateProperty($entity, 'attributes', $attributes);

        $time = $entity->created_at;

        $this->assertInstanceOf(Time::class, $time);
        $this->assertCloseEnoughString(date('Y-m-d H:i:s', $stamp), $time->format('Y-m-d H:i:s'));
    }

    public function testDateMutationFromDatetime(): void
    {
        $dt         = new DateTime('now');
        $entity     = $this->getEntity();
        $attributes = ['created_at' => $dt];
        $this->setPrivateProperty($entity, 'attributes', $attributes);

        $time = $entity->created_at;

        $this->assertInstanceOf(Time::class, $time);
        $this->assertCloseEnoughString($dt->format('Y-m-d H:i:s'), $time->format('Y-m-d H:i:s'));
    }

    public function testDateMutationFromTime(): void
    {
        $dt         = Time::now();
        $entity     = $this->getEntity();
        $attributes = ['created_at' => $dt];
        $this->setPrivateProperty($entity, 'attributes', $attributes);

        $time = $entity->created_at;

        $this->assertInstanceOf(Time::class, $time);
        $this->assertCloseEnoughString($dt->format('Y-m-d H:i:s'), $time->format('Y-m-d H:i:s'));
    }

    public function testDateMutationStringToTime(): void
    {
        $entity = $this->getEntity();

        $entity->created_at = '2017-07-15 13:23:34';

        $time = $this->getPrivateProperty($entity, 'attributes')['created_at'];
        $this->assertInstanceOf(Time::class, $time);
        $this->assertSame('2017-07-15 13:23:34', $time->format('Y-m-d H:i:s'));
    }

    public function testDateMutationTimestampToTime(): void
    {
        $stamp  = time();
        $entity = $this->getEntity();

        $entity->created_at = $stamp;

        $time = $this->getPrivateProperty($entity, 'attributes')['created_at'];
        $this->assertInstanceOf(Time::class, $time);
        $this->assertCloseEnoughString(date('Y-m-d H:i:s'), $time->format('Y-m-d H:i:s'));
    }

    public function testDateMutationDatetimeToTime(): void
    {
        $dt     = new DateTime('now');
        $entity = $this->getEntity();

        $entity->created_at = $dt;

        $time = $this->getPrivateProperty($entity, 'attributes')['created_at'];
        $this->assertInstanceOf(Time::class, $time);
        $this->assertCloseEnoughString($dt->format('Y-m-d H:i:s'), $time->format('Y-m-d H:i:s'));
    }

    public function testDateMutationTimeToTime(): void
    {
        $dt     = Time::now();
        $entity = $this->getEntity();

        $entity->created_at = $dt;

        $time = $this->getPrivateProperty($entity, 'attributes')['created_at'];
        $this->assertInstanceOf(Time::class, $time);
        $this->assertCloseEnoughString($dt->format('Y-m-d H:i:s'), $time->format('Y-m-d H:i:s'));
    }

    public function testCastInteger(): void
    {
        $entity = $this->getCastEntity();

        $entity->first = 3.1;

        $this->assertIsInt($entity->first);
        $this->assertSame(3, $entity->first);

        $entity->first = 3.6;

        $this->assertSame(3, $entity->first);
    }

    public function testCastIntBool(): void
    {
        $entity = new class () extends Entity {
            protected $casts = [
                'active' => 'int-bool',
            ];
        };

        $entity->injectRawData(['active' => '1']);

        $this->assertTrue($entity->active);

        $entity->injectRawData(['active' => '0']);

        $this->assertFalse($entity->active);

        $entity->active = true;

        $this->assertTrue($entity->active);
        $this->assertSame(['active' => 1], $entity->toRawArray());

        $entity->active = false;

        $this->assertFalse($entity->active);
        $this->assertSame(['active' => 0], $entity->toRawArray());
    }

    public function testCastFloat(): void
    {
        $entity = $this->getCastEntity();

        $entity->second = 3;

        $this->assertIsFloat($entity->second);
        $this->assertEqualsWithDelta(3.0, $entity->second, PHP_FLOAT_EPSILON);

        $entity->second = '3.6';

        $this->assertIsFloat($entity->second);
        $this->assertEqualsWithDelta(3.6, $entity->second, PHP_FLOAT_EPSILON);
    }

    public function testCastDouble(): void
    {
        $entity = $this->getCastEntity();

        $entity->third = 3;

        $this->assertIsFloat($entity->third);
        $this->assertEqualsWithDelta(3.0, $entity->third, PHP_FLOAT_EPSILON);

        $entity->third = '3.6';

        $this->assertIsFloat($entity->third);
        $this->assertEqualsWithDelta(3.6, $entity->third, PHP_FLOAT_EPSILON);
    }

    public function testCastString(): void
    {
        $entity = $this->getCastEntity();

        $entity->fourth = 3.1415;

        $this->assertIsString($entity->fourth);
        $this->assertSame('3.1415', $entity->fourth);
    }

    public function testCastBoolean(): void
    {
        $entity = $this->getCastEntity();

        $entity->fifth = 1;

        $this->assertIsBool($entity->fifth);
        $this->assertTrue($entity->fifth);

        $entity->fifth = 0;

        $this->assertIsBool($entity->fifth);
        $this->assertFalse($entity->fifth);
    }

    public function testCastCSV(): void
    {
        $entity          = $this->getCastEntity();
        $data            = ['foo', 'bar', 'bam'];
        $entity->twelfth = $data;

        $result = $entity->toRawArray();

        $this->assertIsString($result['twelfth']);
        $this->assertSame('foo,bar,bam', $result['twelfth']);

        $this->assertIsArray($entity->twelfth);
        $this->assertSame($data, $entity->twelfth);
    }

    public function testCastObject(): void
    {
        $entity = $this->getCastEntity();

        $data          = ['foo' => 'bar'];
        $entity->sixth = $data;

        $this->assertIsObject($entity->sixth);
        $this->assertInstanceOf('stdClass', $entity->sixth);
        $this->assertSame($data, (array) $entity->sixth);
    }

    public function testCastDateTime(): void
    {
        $entity = $this->getCastEntity();

        $entity->eighth = 'March 12, 2017';

        $this->assertInstanceOf(DateTimeInterface::class, $entity->eighth);
        $this->assertSame('2017-03-12', $entity->eighth->format('Y-m-d'));
    }

    public function testCastDateTimeWithTimestampTimezone(): void
    {
        // Save the current timezone.
        $tz = date_default_timezone_get();

        // Change the timezone other than UTC.
        date_default_timezone_set('Asia/Tokyo'); // +09:00

        $entity = $this->getCastEntity();

        $entity->eighth = 1722988800; // 2024-08-07 00:00:00 UTC

        $this->assertInstanceOf(DateTimeInterface::class, $entity->eighth);
        // The timezone is the default timezone, not UTC.
        $this->assertSame('2024-08-07 09:00:00', $entity->eighth->format('Y-m-d H:i:s'));
        $this->assertSame('Asia/Tokyo', $entity->eighth->getTimezoneName());

        // Restore timezone.
        date_default_timezone_set($tz);
    }

    public function testCastTimestamp(): void
    {
        $entity = $this->getCastEntity();

        $date          = 'March 12, 2017';
        $entity->ninth = $date;

        $this->assertIsInt($entity->ninth);
        $this->assertSame(strtotime($date), $entity->ninth);
    }

    public function testCastTimestampException(): void
    {
        $this->expectException(CastException::class);
        $this->expectExceptionMessage('Type casting "timestamp" expects a correct timestamp.');

        $entity        = $this->getCastEntity();
        $entity->ninth = 'some string';

        $entity->ninth; // @phpstan-ignore expr.resultUnused
    }

    public function testCastArray(): void
    {
        $entity = $this->getCastEntity();

        $entity->seventh = ['foo' => 'bar'];

        $check = $this->getPrivateProperty($entity, 'attributes')['seventh'];
        $this->assertSame(serialize(['foo' => 'bar']), $check);
        $this->assertSame(['foo' => 'bar'], $entity->seventh);
    }

    public function testCastArrayByStringSerialize(): void
    {
        $entity = $this->getCastEntity();

        $entity->seventh = 'foobar';

        // Should be a serialized string now...
        $check = $this->getPrivateProperty($entity, 'attributes')['seventh'];
        $this->assertSame(serialize('foobar'), $check);

        $this->assertSame(['foobar'], $entity->seventh);
    }

    public function testCastArrayByArraySerialize(): void
    {
        $entity = $this->getCastEntity();

        $entity->seventh = ['foo' => 'bar'];

        // Should be a serialized string now...
        $check = $this->getPrivateProperty($entity, 'attributes')['seventh'];
        $this->assertSame(serialize(['foo' => 'bar']), $check);

        $this->assertSame(['foo' => 'bar'], $entity->seventh);
    }

    public function testCastArrayByFill(): void
    {
        $entity = $this->getCastEntity();

        $data = ['seventh' => [1, 2, 3]];
        $entity->fill($data);

        // Check if serialiazed
        $check = $this->getPrivateProperty($entity, 'attributes')['seventh'];
        $this->assertSame(serialize([1, 2, 3]), $check);
        // Check if unserialized
        $this->assertSame([1, 2, 3], $entity->seventh);
    }

    public function testCastArrayByConstructor(): void
    {
        $data   = ['seventh' => [1, 2, 3]];
        $entity = $this->getCastEntity($data);

        // Check if serialiazed
        $check = $this->getPrivateProperty($entity, 'attributes')['seventh'];
        $this->assertSame(serialize([1, 2, 3]), $check);
        // Check if unserialized
        $this->assertSame([1, 2, 3], $entity->seventh);
    }

    public function testCastNullable(): void
    {
        $entity = $this->getCastNullableEntity();

        $this->assertNull($entity->string_null);
        $this->assertSame('', $entity->string_empty);
        $this->assertNull($entity->integer_null);
        $this->assertSame(0, $entity->integer_0);
        $this->assertSame('value', $entity->string_value_not_null);
    }

    public function testCastURI(): void
    {
        $entity = $this->getCastEntity();

        $data               = 'https://codeigniter.com/banana';
        $entity->thirteenth = $data;

        $this->assertInstanceOf(URI::class, $entity->thirteenth);
        $this->assertSame($data, (string) $entity->thirteenth);
        $this->assertSame('/banana', $entity->thirteenth->getPath());
    }

    public function testURICastURI(): void
    {
        $entity = $this->getCastEntity();

        $data               = 'https://codeigniter.com/banana';
        $entity->thirteenth = new URI($data);

        $this->assertInstanceOf(URI::class, $entity->thirteenth);
        $this->assertSame($data, (string) $entity->thirteenth);
        $this->assertSame('/banana', $entity->thirteenth->getPath());
    }

    public function testCastAsJSON(): void
    {
        $entity = $this->getCastEntity();

        $entity->tenth = ['foo' => 'bar'];

        // Should be a JSON-encoded string now...
        $check = $this->getPrivateProperty($entity, 'attributes')['tenth'];
        $this->assertSame('{"foo":"bar"}', $check);

        $this->assertInstanceOf('stdClass', $entity->tenth);
        $this->assertSame(['foo' => 'bar'], (array) $entity->tenth);
    }

    public function testCastAsJSONArray(): void
    {
        $entity = $this->getCastEntity();

        $data             = ['Sun', 'Mon', 'Tue'];
        $entity->eleventh = $data;

        // Should be a JSON-encoded string now...
        $check = $this->getPrivateProperty($entity, 'attributes')['eleventh'];
        $this->assertSame('["Sun","Mon","Tue"]', $check);

        $this->assertSame($data, $entity->eleventh);
    }

    public function testCastAsJsonByFill(): void
    {
        $entity = $this->getCastEntity();

        $data = ['eleventh' => [1, 2, 3]];
        $entity->fill($data);

        // Check if serialiazed
        $check = $this->getPrivateProperty($entity, 'attributes')['eleventh'];
        $this->assertSame(json_encode([1, 2, 3]), $check);
        // Check if unserialized
        $this->assertSame([1, 2, 3], $entity->eleventh);
    }

    public function testCastAsJsonByConstructor(): void
    {
        $data   = ['eleventh' => [1, 2, 3]];
        $entity = $this->getCastEntity($data);

        // Check if serialiazed
        $check = $this->getPrivateProperty($entity, 'attributes')['eleventh'];
        $this->assertSame(json_encode([1, 2, 3]), $check);
        // Check if unserialized
        $this->assertSame([1, 2, 3], $entity->eleventh);
    }

    public function testCastAsJSONErrorDepth(): void
    {
        $this->expectException(CastException::class);
        $this->expectExceptionMessage('Maximum stack depth exceeded');

        $entity = $this->getCastEntity();

        // Create array with depth 513 to get depth error
        $array   = [];
        $value   = 'test value';
        $keys    = rtrim(str_repeat('test.', 513), '.');
        $keys    = explode('.', $keys);
        $current = &$array;

        foreach ($keys as $key) {
            $current = &$current[$key];
        }
        $current       = $value;
        $entity->tenth = $array;
    }

    public function testCastAsJSONErrorUTF8(): void
    {
        $this->expectException(CastException::class);
        $this->expectExceptionMessage('Malformed UTF-8 characters, possibly incorrectly encoded');

        $entity = $this->getCastEntity();

        $entity->tenth = "\xB1\x31";
    }

    /**
     * @psalm-suppress InaccessibleMethod
     */
    public function testCastAsJSONSyntaxError(): void
    {
        $this->expectException(CastException::class);
        $this->expectExceptionMessage('Syntax error, malformed JSON');

        (Closure::bind(static function (string $value) {
            $entity                 = new Entity();
            $entity->casts['dummy'] = 'json[array]';

            return $entity->castAs($value, 'dummy');
        }, null, Entity::class))('{ this is bad string');
    }

    /**
     * @psalm-suppress InaccessibleMethod
     */
    public function testCastAsJSONAnotherErrorDepth(): void
    {
        $this->expectException(CastException::class);
        $this->expectExceptionMessage('Maximum stack depth exceeded');

        $string = '{' . str_repeat('"test":{', 513) . '"test":"value"' . str_repeat('}', 513) . '}';
        (Closure::bind(static function (string $value) {
            $entity                 = new Entity();
            $entity->casts['dummy'] = 'json[array]';

            return $entity->castAs($value, 'dummy');
        }, null, Entity::class))($string);
    }

    /**
     * @psalm-suppress InaccessibleMethod
     */
    public function testCastAsJSONControlCharCheck(): void
    {
        $this->expectException(CastException::class);
        $this->expectExceptionMessage('Unexpected control character found');

        $string = "{\n\t\"property1\": \"The quick brown fox\njumps over the lazy dog\",\n\t\"property2\":\"value2\"\n}";
        (Closure::bind(static function (string $value) {
            $entity                 = new Entity();
            $entity->casts['dummy'] = 'json[array]';

            return $entity->castAs($value, 'dummy');
        }, null, Entity::class))($string);
    }

    /**
     * @psalm-suppress InaccessibleMethod
     */
    public function testCastAsJSONStateMismatch(): void
    {
        $this->expectException(CastException::class);
        $this->expectExceptionMessage('Underflow or the modes mismatch');

        $string = '[{"name":"jack","product_id":"1234"]';
        (Closure::bind(static function (string $value) {
            $entity                 = new Entity();
            $entity->casts['dummy'] = 'json[array]';

            return $entity->castAs($value, 'dummy');
        }, null, Entity::class))($string);
    }

    public function testCastSetter(): void
    {
        $string        = '321 String with numbers 123';
        $entity        = $this->getCastEntity();
        $entity->first = $string;

        $entity->cast(false);

        $this->assertIsString($entity->first);
        $this->assertSame($string, $entity->first);

        $entity->cast(true);

        $this->assertIsInt($entity->first);
        $this->assertSame((int) $string, $entity->first);
    }

    public function testCastGetter(): void
    {
        $entity = new Entity();

        $this->assertIsBool($entity->cast());
    }

    public function testCustomCast(): void
    {
        $entity = $this->getCustomCastEntity();

        $entity->first = 'base 64';

        $fieldValue = $this->getPrivateProperty($entity, 'attributes')['first'];
        $this->assertSame(base64_encode('base 64'), $fieldValue);
        $this->assertSame('base 64', $entity->first);
    }

    public function testCustomCastException(): void
    {
        $this->expectException(CastException::class);
        $this->expectExceptionMessage('The "Tests\Support\Entity\Cast\NotExtendsBaseCast" class must inherit the "CodeIgniter\Entity\Cast\BaseCast" class');

        $entity = $this->getCustomCastEntity();

        $entity->second = 'throw Exception';
    }

    public function testCustomCastParams(): void
    {
        $entity = $this->getCustomCastEntity();

        $entity->third = 'value';

        $this->assertSame('value:["param1","param2","param3"]', $entity->third);

        $entity->fourth = 'test_nullable_type';

        $this->assertSame('test_nullable_type:["nullable"]', $entity->fourth);
    }

    public function testCastEnumStringBacked(): void
    {
        $entity = new class () extends Entity {
            protected $casts = [
                'status' => 'enum[' . StatusEnum::class . ']',
            ];
        };

        $entity->status = 'active';

        $this->assertInstanceOf(StatusEnum::class, $entity->status);
        $this->assertSame(StatusEnum::ACTIVE, $entity->status);
        $this->assertSame(['status' => 'active'], $entity->toRawArray());
    }

    public function testCastEnumIntBacked(): void
    {
        $entity = new class () extends Entity {
            protected $casts = [
                'role' => 'enum[' . RoleEnum::class . ']',
            ];
        };

        $entity->role = 2;

        $this->assertInstanceOf(RoleEnum::class, $entity->role);
        $this->assertSame(RoleEnum::ADMIN, $entity->role);
        $this->assertSame(['role' => 2], $entity->toRawArray());
    }

    public function testCastEnumUnit(): void
    {
        $entity = new class () extends Entity {
            protected $casts = [
                'color' => 'enum[' . ColorEnum::class . ']',
            ];
        };

        $entity->color = 'RED';

        $this->assertInstanceOf(ColorEnum::class, $entity->color);
        $this->assertSame(ColorEnum::RED, $entity->color);
        $this->assertSame(['color' => 'RED'], $entity->toRawArray());
    }

    public function testCastEnumNullable(): void
    {
        $entity = new class () extends Entity {
            protected $casts = [
                'status' => '?enum[' . StatusEnum::class . ']',
            ];
        };

        $entity->status = null;

        $this->assertNull($entity->status);

        $entity->status = 'pending';

        $this->assertInstanceOf(StatusEnum::class, $entity->status);
        $this->assertSame(StatusEnum::PENDING, $entity->status);
    }

    #[DataProvider('provideCastEnumExceptions')]
    public function testCastEnumExceptions(string $castType, mixed $value, string $property, string $message, bool $useInject): void
    {
        $this->expectException(CastException::class);
        $this->expectExceptionMessage($message);

        $entity = new class ($castType, $property) extends Entity {
            protected $casts = [];

            public function __construct(string $castType, string $property)
            {
                $this->casts[$property] = $castType;
                parent::__construct();
            }
        };

        if ($useInject) {
            // Inject raw data to bypass set() validation and test get() method
            $entity->injectRawData([$property => $value]);
            // Trigger get() method by accessing property
            $entity->{$property}; // @phpstan-ignore expr.resultUnused
        } else {
            // Test set() method directly
            $entity->{$property} = $value;
        }
    }

    /**
     * @return iterable<string, array<string, bool|string>>
     */
    public static function provideCastEnumExceptions(): iterable
    {
        return [
            'missing class' => [
                'castType'  => 'enum',
                'value'     => 'active',
                'property'  => 'status',
                'message'   => 'Enum class must be specified for enum casting',
                'useInject' => false,
            ],
            'not enum' => [
                'castType'  => 'enum[stdClass]',
                'value'     => 'active',
                'property'  => 'status',
                'message'   => 'The "stdClass" is not a valid enum class',
                'useInject' => false,
            ],
            'invalid backed enum value' => [
                'castType'  => 'enum[' . StatusEnum::class . ']',
                'value'     => 'invalid_status',
                'property'  => 'status',
                'message'   => 'Invalid value "invalid_status" for enum "Tests\Support\Enum\StatusEnum"',
                'useInject' => false,
            ],
            'invalid unit enum case name' => [
                'castType'  => 'enum[' . ColorEnum::class . ']',
                'value'     => 'YELLOW',
                'property'  => 'color',
                'message'   => 'Invalid case name "YELLOW" for enum "Tests\Support\Enum\ColorEnum"',
                'useInject' => false,
            ],
            'invalid enum type' => [
                'castType'  => 'enum[' . StatusEnum::class . ']',
                'value'     => ColorEnum::RED,
                'property'  => 'status',
                'message'   => 'Expected enum of type "Tests\Support\Enum\StatusEnum", but received "Tests\Support\Enum\ColorEnum"',
                'useInject' => false,
            ],
            'get missing class' => [
                'castType'  => 'enum',
                'value'     => 'active',
                'property'  => 'status',
                'message'   => 'Enum class must be specified for enum casting',
                'useInject' => true,
            ],
            'get not enum' => [
                'castType'  => 'enum[stdClass]',
                'value'     => 'active',
                'property'  => 'status',
                'message'   => 'The "stdClass" is not a valid enum class',
                'useInject' => true,
            ],
            'get invalid backed enum value' => [
                'castType'  => 'enum[' . StatusEnum::class . ']',
                'value'     => 'invalid_status',
                'property'  => 'status',
                'message'   => 'Invalid value "invalid_status" for enum "Tests\Support\Enum\StatusEnum"',
                'useInject' => true,
            ],
            'get invalid unit enum case name' => [
                'castType'  => 'enum[' . ColorEnum::class . ']',
                'value'     => 'YELLOW',
                'property'  => 'color',
                'message'   => 'Invalid case name "YELLOW" for enum "Tests\Support\Enum\ColorEnum"',
                'useInject' => true,
            ],
        ];
    }

    public function testCastEnumSetWithBackedEnumObject(): void
    {
        $entity = new class () extends Entity {
            protected $casts = [
                'status' => 'enum[' . StatusEnum::class . ']',
            ];
        };

        // Assign an enum object directly
        $entity->status = StatusEnum::ACTIVE;

        // Should extract the backing value for storage
        $this->assertSame(['status' => 'active'], $entity->toRawArray());
        // Should return the enum object when accessed
        $this->assertInstanceOf(StatusEnum::class, $entity->status);
        $this->assertSame(StatusEnum::ACTIVE, $entity->status);
    }

    public function testCastEnumSetWithUnitEnumObject(): void
    {
        $entity = new class () extends Entity {
            protected $casts = [
                'color' => 'enum[' . ColorEnum::class . ']',
            ];
        };

        // Assign a unit enum object directly
        $entity->color = ColorEnum::RED;

        // Should extract the case name for storage
        $this->assertSame(['color' => 'RED'], $entity->toRawArray());
        // Should return the enum object when accessed
        $this->assertInstanceOf(ColorEnum::class, $entity->color);
        $this->assertSame(ColorEnum::RED, $entity->color);
    }

    public function testAsArray(): void
    {
        $entity = $this->getEntity();

        $result = $entity->toArray();

        $this->assertSame([
            'foo'       => null,
            'bar'       => ':bar',
            'default'   => 'sumfin',
            'createdAt' => null,
        ], $result, );
    }

    public function testAsArrayRecursive(): void
    {
        $entity         = $this->getEntity();
        $entity->entity = $this->getEntity();

        $result = $entity->toArray(false, true, true);

        $this->assertSame([
            'foo'     => null,
            'bar'     => ':bar',
            'default' => 'sumfin',
            'entity'  => [
                'foo'       => null,
                'bar'       => ':bar',
                'default'   => 'sumfin',
                'createdAt' => null,
            ],
            'createdAt' => null,
        ], $result);
    }

    public function testAsArrayMapped(): void
    {
        $entity = $this->getMappedEntity();

        $result = $entity->toArray();

        $this->assertSame([
            'bar'  => null,
            'orig' => ':oo',
        ], $result);
    }

    public function testAsArraySwapped(): void
    {
        $entity = $this->getSwappedEntity();

        $result = $entity->toArray();

        $this->assertSame([
            'bar'          => 'foo',
            'foo'          => 'bar',
            'original_bar' => 'bar',
        ], $result);
    }

    public function testAsArrayRestoringCastStatus(): void
    {
        $entity = new class () extends Entity {
            protected $attributes = [
                'first' => null,
            ];
            protected $original = [
                'first' => null,
            ];
            protected $casts = [
                'first' => 'integer',
            ];
        };
        $entity->first = '2026 Year';

        // Disabled casting properties, but we will allow casting in the method.
        $entity->cast(false);
        $beforeCast = $entity->cast();
        $result     = $entity->toArray(true, true);

        $this->assertSame(2026, $result['first']);
        $this->assertSame($beforeCast, $entity->cast());

        // Enabled casting properties, but we will disallow casting in the method.
        $entity->cast(true);
        $beforeCast = $entity->cast();
        $result     = $entity->toArray(true, false);

        $this->assertSame('2026 Year', $result['first']);
        $this->assertSame($beforeCast, $entity->cast());
    }

    public function testDataMappingIssetSwapped(): void
    {
        $entity = $this->getSimpleSwappedEntity();

        $entity->foo = '111';
        $entity->bar = '222';

        $isset = isset($entity->foo);
        $this->assertTrue($isset);
        $this->assertSame('111', $entity->foo);

        $isset = isset($entity->bar);
        $this->assertTrue($isset);
        $this->assertSame('222', $entity->bar);

        $result = $entity->toRawArray();

        $this->assertSame([
            'foo' => '222',
            'bar' => '111',
        ], $result);
    }

    public function testDataMappingIssetUnsetSwapped(): void
    {
        $entity = $this->getSimpleSwappedEntity();

        $entity->foo = '111';
        $entity->bar = '222';
        unset($entity->foo);

        $isset = isset($entity->foo);
        $this->assertFalse($isset);
        $this->assertNull($entity->foo);

        $isset = isset($entity->bar);
        $this->assertTrue($isset);
        $this->assertSame('222', $entity->bar);
    }

    public function testToArraySkipAttributesWithUnderscoreInFirstCharacter(): void
    {
        $entity = new class () extends Entity {
            protected $attributes = [
                '_foo' => null,
                'bar'  => null,
            ];
        };

        $result = $entity->toArray();

        $this->assertSame([
            'bar' => null,
        ], $result);
    }

    public function testAsArrayOnlyChanged(): void
    {
        $entity      = $this->getEntity();
        $entity->bar = 'foo';

        $result = $entity->toArray(true);

        $this->assertSame([
            'bar' => 'bar:foo:bar',
        ], $result);
    }

    public function testToRawArray(): void
    {
        $entity = $this->getEntity();

        $result = $entity->toRawArray();

        $this->assertSame([
            'foo'        => null,
            'bar'        => null,
            'default'    => 'sumfin',
            'created_at' => null,
        ], $result);
    }

    public function testToRawArrayRecursive(): void
    {
        $entity         = $this->getEntity();
        $entity->entity = $this->getEntity();

        $result = $entity->toRawArray(false, true);

        $this->assertSame([
            'foo'        => null,
            'bar'        => null,
            'default'    => 'sumfin',
            'created_at' => null,
            'entity'     => [
                'foo'        => null,
                'bar'        => null,
                'default'    => 'sumfin',
                'created_at' => null,
            ],
        ], $result);
    }

    public function testToRawArrayRecursiveWithArray(): void
    {
        $entity           = $this->getEntity();
        $entity->entities = [$this->getEntity(), $this->getEntity()];

        $result = $entity->toRawArray(false, true);

        $this->assertSame([
            'foo'        => null,
            'bar'        => null,
            'default'    => 'sumfin',
            'created_at' => null,
            'entities'   => [[
                'foo'        => null,
                'bar'        => null,
                'default'    => 'sumfin',
                'created_at' => null,
            ], [
                'foo'        => null,
                'bar'        => null,
                'default'    => 'sumfin',
                'created_at' => null,
            ]],
        ], $result);
    }

    public function testToRawArrayRecursiveOnlyChangedWithArray(): void
    {
        $first  = $this->getEntity();
        $second = $this->getEntity();

        $entity           = $this->getEntity();
        $entity->entities = [$first];
        $entity->syncOriginal();

        $entity->entities = [$first, $second];

        $result = $entity->toRawArray(true, true);

        $this->assertSame([
            'entities' => [1 => [
                'foo'        => null,
                'bar'        => null,
                'default'    => 'sumfin',
                'created_at' => null,
            ]],
        ], $result);
    }

    public function testToRawArrayRecursiveOnlyChangedWithArrayEntityModified(): void
    {
        $first       = $this->getEntity();
        $second      = $this->getEntity();
        $first->foo  = 'original';
        $second->foo = 'also_original';

        $entity           = $this->getEntity();
        $entity->entities = [$first, $second];
        $entity->syncOriginal();

        $second->foo = 'modified';

        $result = $entity->toRawArray(true, true);

        $this->assertSame([
            'entities' => [1 => [
                'foo'        => 'modified',
                'bar'        => null,
                'default'    => 'sumfin',
                'created_at' => null,
            ]],
        ], $result);
    }

    public function testToRawArrayRecursiveOnlyChangedWithArrayMultipleEntitiesModified(): void
    {
        $first       = $this->getEntity();
        $second      = $this->getEntity();
        $third       = $this->getEntity();
        $first->foo  = 'first';
        $second->foo = 'second';
        $third->foo  = 'third';

        $entity           = $this->getEntity();
        $entity->entities = [$first, $second, $third];
        $entity->syncOriginal();

        $first->foo = 'first_modified';
        $third->foo = 'third_modified';

        $result = $entity->toRawArray(true, true);

        $this->assertSame([
            'entities' => [
                0 => [
                    'foo'        => 'first_modified',
                    'bar'        => null,
                    'default'    => 'sumfin',
                    'created_at' => null,
                ],
                2 => [
                    'foo'        => 'third_modified',
                    'bar'        => null,
                    'default'    => 'sumfin',
                    'created_at' => null,
                ],
            ],
        ], $result);
    }

    public function testToRawArrayRecursiveOnlyChangedWithArrayNoEntitiesModified(): void
    {
        $first       = $this->getEntity();
        $second      = $this->getEntity();
        $first->foo  = 'unchanged';
        $second->foo = 'also_unchanged';

        $entity           = $this->getEntity();
        $entity->entities = [$first, $second];
        $entity->syncOriginal();

        $result = $entity->toRawArray(true, true);

        $this->assertSame([], $result);
    }

    public function testToRawArrayOnlyChanged(): void
    {
        $entity      = $this->getEntity();
        $entity->bar = 'foo';

        $result = $entity->toRawArray(true);

        $this->assertSame([
            'bar' => 'bar:foo',
        ], $result);
    }

    public function testFilledConstruction(): void
    {
        $data = [
            'foo' => 'bar',
            'bar' => 'baz',
        ];
        $something = new SomeEntity($data);

        $this->assertSame('bar', $something->foo);
        $this->assertSame('baz', $something->bar);
    }

    public function testChangedArray(): void
    {
        $data      = ['bar' => 'baz'];
        $something = new SomeEntity($data);

        $this->assertSame($data, $something->toArray(true));

        $something->magic = 'rockin';

        $this->assertSame([
            'foo'   => null,
            'bar'   => 'baz',
            'magic' => 'rockin',
        ], $something->toArray(false));
    }

    public function testHasChangedNotExists(): void
    {
        $entity = new SomeEntity();

        $this->assertFalse($entity->hasChanged('foo'));
    }

    public function testHasChangedNewElement(): void
    {
        $entity = new SomeEntity();

        $entity->foo = 'bar';

        $this->assertTrue($entity->hasChanged('foo'));
    }

    public function testHasChangedNoChange(): void
    {
        $entity = $this->getEntity();

        $this->assertFalse($entity->hasChanged('default'));
    }

    public function testHasChangedMappedNoChange(): void
    {
        $entity = $this->getEntity();

        $entity->createdAt = null;

        $this->assertFalse($entity->hasChanged('createdAt'));
    }

    public function testHasChangedMappedChanged(): void
    {
        $entity = $this->getEntity();

        $entity->createdAt = '2022-11-11 11:11:11';

        $this->assertTrue($entity->hasChanged('createdAt'));
    }

    public function testHasChangedWholeEntity(): void
    {
        $entity = $this->getEntity();

        $entity->foo = 'bar';

        $this->assertTrue($entity->hasChanged());
    }

    public function testHasChangedKeyNotExists(): void
    {
        $entity = $this->getEntity();

        $this->assertFalse($entity->hasChanged('xxx'));
    }

    public function testDataMappingIssetSetGetMethod(): void
    {
        $entity = $this->getEntity();

        $entity->created_at = '12345678';

        $issetReturn = isset($entity->createdAt);
        $this->assertTrue($issetReturn);

        $entity->bar = 'foo';

        $issetReturn = isset($entity->FakeBar);
        $this->assertTrue($issetReturn);
    }

    public function testJsonSerializableEntity(): void
    {
        $entity = $this->getEntity();
        $entity->setBar('foo');

        $this->assertSame(json_encode($entity->toArray()), json_encode($entity));
    }

    public function testDataCasterInit(): void
    {
        $entity = new class () extends Entity {
            protected $attributes = [
                'first' => '12345',
            ];
            protected $casts = [
                'first' => 'integer',
            ];
        };

        $getDataCaster = $this->getPrivateMethodInvoker($entity, 'dataCaster');

        $this->assertInstanceOf(DataCaster::class, $getDataCaster());
        $this->assertInstanceOf(DataCaster::class, $this->getPrivateProperty($entity, 'dataCaster'));
        $this->assertSame(12345, $entity->first);

        // Disable casting, but the DataCaster is initialized
        $entity->cast(false);
        $this->assertInstanceOf(DataCaster::class, $getDataCaster());
        $this->assertInstanceOf(DataCaster::class, $this->getPrivateProperty($entity, 'dataCaster'));
        $this->assertIsString($entity->first);

        // Method castAs() ignore on the $_cast option
        $this->assertSame(12345, $this->getPrivateMethodInvoker($entity, 'castAs')('12345', 'first'));

        // Restore casting
        $entity->cast(true);
        $this->assertInstanceOf(DataCaster::class, $getDataCaster());
        $this->assertInstanceOf(DataCaster::class, $this->getPrivateProperty($entity, 'dataCaster'));
        $this->assertSame(12345, $entity->first);
    }

    public function testDataCasterInitEmptyCasts(): void
    {
        $entity = new class () extends Entity {
            protected $attributes = [
                'first' => '12345',
            ];
            protected $casts = [];
        };

        $getDataCaster = $this->getPrivateMethodInvoker($entity, 'dataCaster');

        $this->assertNull($getDataCaster());
        $this->assertNull($this->getPrivateProperty($entity, 'dataCaster'));
        $this->assertSame('12345', $entity->first);

        // Disable casting, the DataCaster was not initialized
        $entity->cast(false);
        $this->assertNull($getDataCaster());
        $this->assertNull($this->getPrivateProperty($entity, 'dataCaster'));
        $this->assertSame('12345', $entity->first);

        // Method castAs() depends on the $_cast option
        $this->assertSame('12345', $this->getPrivateMethodInvoker($entity, 'castAs')('12345', 'first'));

        // Restore casting
        $entity->cast(true);
        $this->assertNull($getDataCaster());
        $this->assertNull($this->getPrivateProperty($entity, 'dataCaster'));
        $this->assertSame('12345', $entity->first);
    }

    private function getEntity(): object
    {
        return new class () extends Entity {
            protected $attributes = [
                'foo'        => null,
                'bar'        => null,
                'default'    => 'sumfin',
                'created_at' => null,
            ];
            protected $original = [
                'foo'        => null,
                'bar'        => null,
                'default'    => 'sumfin',
                'created_at' => null,
            ];
            protected $datamap = [
                'createdAt' => 'created_at',
            ];

            public function setBar(int|string $value): self
            {
                $this->attributes['bar'] = "bar:{$value}";

                return $this;
            }

            public function getBar(): string
            {
                return "{$this->attributes['bar']}:bar";
            }

            public function getFakeBar(): string
            {
                return "{$this->attributes['bar']}:bar";
            }
        };
    }

    private function getNewSetterGetterEntity(): object
    {
        return new class () extends Entity {
            protected $attributes = [
                'foo'        => null,
                'bar'        => null,
                'default'    => 'sumfin',
                'created_at' => null,
            ];
            protected $original = [
                'foo'        => null,
                'bar'        => null,
                'default'    => 'sumfin',
                'created_at' => null,
            ];
            protected $datamap = [
                'createdAt' => 'created_at',
            ];
            private string $bar;

            public function setBar(string $value): self
            {
                $this->bar = $value;

                return $this;
            }

            public function getBar(): string
            {
                return $this->bar;
            }

            public function _setBar(string $value): self
            {
                $this->attributes['bar'] = "bar:{$value}";

                return $this;
            }

            public function _getBar(): string
            {
                return "{$this->attributes['bar']}:bar";
            }
        };
    }

    private function getMappedEntity(): object
    {
        return new class () extends Entity {
            protected $attributes = [
                'foo'    => null,
                'simple' => null,
            ];
            protected $original = [
                'foo'    => null,
                'simple' => null,
            ];

            // 'bar' is class property, 'foo' is db column
            protected $datamap = [
                'bar'  => 'foo',
                'orig' => 'simple',
            ];

            protected function setSimple(string $val): void
            {
                $this->attributes['simple'] = 'oo:' . $val;
            }

            protected function getSimple(): string
            {
                return $this->attributes['simple'] . ':oo';
            }
        };
    }

    private function getSwappedEntity(): object
    {
        return new class () extends Entity {
            protected $attributes = [
                'foo' => 'foo',
                'bar' => 'bar',
            ];
            protected $original = [
                'foo' => 'foo',
                'bar' => 'bar',
            ];
            protected $datamap = [
                'bar'          => 'foo',
                'foo'          => 'bar',
                'original_bar' => 'bar',
            ];
        };
    }

    private function getSimpleSwappedEntity(): object
    {
        return new class () extends Entity {
            protected $attributes = [
                'foo' => 'foo',
                'bar' => 'bar',
            ];
            protected $original = [
                'foo' => 'foo',
                'bar' => 'bar',
            ];
            protected $datamap = [
                'bar' => 'foo',
                'foo' => 'bar',
            ];
        };
    }

    private function getCastEntity($data = null): object
    {
        return new class ($data) extends Entity {
            protected $attributes = [
                'first'      => null,
                'second'     => null,
                'third'      => null,
                'fourth'     => null,
                'fifth'      => null,
                'sixth'      => null,
                'seventh'    => null,
                'eighth'     => null,
                'ninth'      => null,
                'tenth'      => null,
                'eleventh'   => null,
                'twelfth'    => null,
                'thirteenth' => null,
            ];
            protected $original = [
                'first'      => null,
                'second'     => null,
                'third'      => null,
                'fourth'     => null,
                'fifth'      => null,
                'sixth'      => null,
                'seventh'    => null,
                'eighth'     => null,
                'ninth'      => null,
                'tenth'      => null,
                'eleventh'   => null,
                'twelfth'    => null,
                'thirteenth' => null,
            ];

            // 'bar' is db column, 'foo' is internal representation
            protected $casts = [
                'first'      => 'integer',
                'second'     => 'float',
                'third'      => 'double',
                'fourth'     => 'string',
                'fifth'      => 'boolean',
                'sixth'      => 'object',
                'seventh'    => 'array',
                'eighth'     => 'datetime',
                'ninth'      => 'timestamp',
                'tenth'      => 'json',
                'eleventh'   => 'json-array',
                'twelfth'    => 'csv',
                'thirteenth' => 'uri',
            ];

            public function setSeventh(string $seventh): void
            {
                $this->attributes['seventh'] = $seventh;
            }
        };
    }

    private function getCastNullableEntity(): object
    {
        return new class () extends Entity {
            protected $attributes = [
                'string_null'           => null,
                'string_empty'          => null,
                'integer_null'          => null,
                'integer_0'             => null,
                'string_value_not_null' => 'value',
            ];
            protected $original = [
                'string_null'           => null,
                'string_empty'          => null,
                'integer_null'          => null,
                'integer_0'             => null,
                'string_value_not_null' => 'value',
            ];

            // 'bar' is db column, 'foo' is internal representation
            protected $casts = [
                'string_null'           => '?string',
                'string_empty'          => 'string',
                'integer_null'          => '?integer',
                'integer_0'             => 'integer',
                'string_value_not_null' => '?string',
            ];
        };
    }

    private function getCustomCastEntity(): object
    {
        return new class () extends Entity {
            protected $attributes = [
                'first'  => null,
                'second' => null,
                'third'  => null,
                'fourth' => null,
            ];
            protected $original = [
                'first'  => null,
                'second' => null,
                'third'  => null,
                'fourth' => null,
            ];

            // 'bar' is db column, 'foo' is internal representation
            protected $casts = [
                'first'  => 'base64',
                'second' => 'someType',
                'third'  => 'type[param1, param2,param3]',
                'fourth' => '?type',
            ];
            protected $castHandlers = [
                'base64'   => CastBase64::class,
                'someType' => NotExtendsBaseCast::class,
                'type'     => CastPassParameters::class,
            ];
        };
    }

    public function testHasChangedWithScalarsOnlyUsesOptimization(): void
    {
        $entity = new class () extends Entity {
            protected $attributes = [
                'id'   => 1,
                'name' => 'test',
                'flag' => true,
            ];
        };

        // Sync original to set $_onlyScalars = true
        $entity->syncOriginal();

        $this->assertFalse($entity->hasChanged());

        $entity->id = 2;

        $this->assertTrue($entity->hasChanged());
    }

    public function testHasChangedWithObjectsDoesNotUseOptimization(): void
    {
        $entity = new class () extends Entity {
            protected $attributes = [
                'id'   => 1,
                'data' => null,
            ];
        };

        $entity->data = new stdClass();
        $entity->syncOriginal();

        $this->assertFalse($entity->hasChanged());

        $newObj       = new stdClass();
        $newObj->test = 'value';
        $entity->data = $newObj;

        $this->assertTrue($entity->hasChanged());
    }

    public function testHasChangedDetectsArrayChanges(): void
    {
        $entity = new class () extends Entity {
            protected $attributes = [
                'items' => ['a', 'b', 'c'],
            ];
        };

        $entity->syncOriginal();

        $this->assertFalse($entity->hasChanged('items'));

        $entity->items = ['a', 'b', 'd'];

        $this->assertTrue($entity->hasChanged('items'));
    }

    public function testHasChangedDetectsNestedArrayChanges(): void
    {
        $entity = new class () extends Entity {
            protected $attributes = [
                'data' => [
                    'level1' => [
                        'level2' => 'value',
                    ],
                ],
            ];
        };

        $entity->syncOriginal();

        $this->assertFalse($entity->hasChanged('data'));

        $entity->data = [
            'level1' => [
                'level2' => 'different',
            ],
        ];

        $this->assertTrue($entity->hasChanged('data'));
    }

    public function testHasChangedDetectsObjectPropertyChanges(): void
    {
        $entity = new class () extends Entity {
            protected $attributes = [
                'obj' => null,
            ];
        };

        $obj         = new stdClass();
        $obj->prop   = 'original';
        $entity->obj = $obj;
        $entity->syncOriginal();

        $this->assertFalse($entity->hasChanged('obj'));

        $newObj       = new stdClass();
        $newObj->prop = 'modified';
        $entity->obj  = $newObj;

        $this->assertTrue($entity->hasChanged('obj'));
    }

    public function testHasChangedWithNestedEntity(): void
    {
        $innerEntity = new SomeEntity(['foo' => 'bar']);
        $outerEntity = new class () extends Entity {
            protected $attributes = [
                'nested' => null,
            ];
        };
        $outerEntity->nested = $innerEntity;
        $outerEntity->syncOriginal();

        $this->assertFalse($outerEntity->hasChanged('nested'));

        $newInner            = new SomeEntity(['foo' => 'baz']);
        $outerEntity->nested = $newInner;

        $this->assertTrue($outerEntity->hasChanged('nested'));
    }

    public function testHasChangedWithJsonSerializable(): void
    {
        $entity = new class () extends Entity {
            protected $attributes = [
                'data' => null,
            ];
        };

        $obj1 = new class () implements JsonSerializable {
            public function jsonSerialize(): mixed
            {
                return ['value' => 'original'];
            }
        };

        $entity->data = $obj1;
        $entity->syncOriginal();

        $this->assertFalse($entity->hasChanged('data'));

        $obj2 = new class () implements JsonSerializable {
            public function jsonSerialize(): mixed
            {
                return ['value' => 'modified'];
            }
        };

        $entity->data = $obj2;

        $this->assertTrue($entity->hasChanged('data'));
    }

    public function testHasChangedDoesNotDetectUnchangedObject(): void
    {
        $entity = new class () extends Entity {
            protected $attributes = [
                'obj' => null,
            ];
        };

        $obj         = new stdClass();
        $obj->prop   = 'value';
        $entity->obj = $obj;
        $entity->syncOriginal();

        $sameObj       = new stdClass();
        $sameObj->prop = 'value';
        $entity->obj   = $sameObj;

        $this->assertFalse($entity->hasChanged('obj'));
    }

    public function testSyncOriginalWithMixedTypes(): void
    {
        $entity = new class () extends Entity {
            protected $attributes = [
                'scalar'  => 'text',
                'number'  => 42,
                'array'   => [1, 2, 3],
                'object'  => null,
                'null'    => null,
                'boolean' => true,
            ];
        };

        $obj            = new stdClass();
        $obj->prop      = 'value';
        $entity->object = $obj;

        $entity->syncOriginal();

        $original = $this->getPrivateProperty($entity, 'original');

        // Scalars should be stored as-is
        $this->assertSame('text', $original['scalar']);
        $this->assertSame(42, $original['number']);
        $this->assertNull($original['null']);
        $this->assertTrue($original['boolean']);

        // Objects and arrays should be JSON-encoded
        $this->assertIsString($original['array']);
        $this->assertIsString($original['object']);
        $this->assertSame(json_encode([1, 2, 3]), $original['array']);
    }

    public function testSyncOriginalSetsHasOnlyScalarsFalseWithArrays(): void
    {
        $entity = new class () extends Entity {
            protected $attributes = [
                'id'    => 1,
                'items' => ['a', 'b'],
            ];
        };

        $entity->syncOriginal();

        $original = $this->getPrivateProperty($entity, 'original');
        $this->assertIsString($original['items']);
        $this->assertSame(json_encode(['a', 'b']), $original['items']);
    }

    public function testSyncOriginalSetsHasOnlyScalarsTrueWithOnlyScalars(): void
    {
        $entity = new class () extends Entity {
            protected $attributes = [
                'id'     => 1,
                'name'   => 'test',
                'active' => true,
                'price'  => 99.99,
            ];
        };

        $entity->syncOriginal();

        $original = $this->getPrivateProperty($entity, 'original');
        $this->assertSame(1, $original['id']);
        $this->assertSame('test', $original['name']);
        $this->assertTrue($original['active']);
        $this->assertEqualsWithDelta(99.99, $original['price'], PHP_FLOAT_EPSILON);
    }

    public function testHasChangedWithObjectToArray(): void
    {
        $entity = new class () extends Entity {
            protected $attributes = [
                'data' => null,
            ];
        };

        $entity->data = new stdClass();
        $entity->syncOriginal();

        $entity->data = [];

        $this->assertTrue($entity->hasChanged('data'));
    }

    public function testHasChangedWithRemovedKey(): void
    {
        $entity = new class () extends Entity {
            protected $attributes = [
                'foo' => 'bar',
            ];
        };

        $entity->syncOriginal();

        unset($entity->foo);

        $this->assertTrue($entity->hasChanged('foo'));
    }

    public function testNormalizeValueWithEntityToArray(): void
    {
        $innerEntity = new SomeEntity(['foo' => 'bar', 'bar' => 'baz']);
        $entity      = new class () extends Entity {
            protected $attributes = [
                'nested' => null,
            ];
        };

        $entity->nested = $innerEntity;
        $entity->syncOriginal();

        // Change inner entity property
        $innerEntity2   = new SomeEntity(['foo' => 'changed', 'bar' => 'baz']);
        $entity->nested = $innerEntity2;

        $this->assertTrue($entity->hasChanged('nested'));
    }

    public function testHasChangedWithComplexNestedStructure(): void
    {
        $entity = new class () extends Entity {
            protected $attributes = [
                'complex' => null,
            ];
        };

        $complex = [
            'level1' => [
                'level2' => [
                    'value' => 'original',
                ],
            ],
        ];

        $entity->complex = $complex;
        $entity->syncOriginal();

        $this->assertFalse($entity->hasChanged('complex'));

        // Deep change
        $complex['level1']['level2']['value'] = 'modified';
        $entity->complex                      = $complex;

        $this->assertTrue($entity->hasChanged('complex'));
    }

    public function testHasChangedWithObjectContainingArray(): void
    {
        $entity = new class () extends Entity {
            protected $attributes = [
                'obj' => null,
            ];
        };

        $obj         = new stdClass();
        $obj->items  = ['a', 'b', 'c'];
        $entity->obj = $obj;
        $entity->syncOriginal();

        $this->assertFalse($entity->hasChanged('obj'));

        // Change array inside object
        $newObj        = new stdClass();
        $newObj->items = ['a', 'b', 'd'];
        $entity->obj   = $newObj;

        $this->assertTrue($entity->hasChanged('obj'));
    }

    public function testSyncOriginalAfterMultipleChanges(): void
    {
        $entity = new class () extends Entity {
            protected $attributes = [
                'value' => 'original',
            ];
        };

        $entity->syncOriginal();
        $this->assertFalse($entity->hasChanged());

        $entity->value = 'changed1';
        $this->assertTrue($entity->hasChanged());

        $entity->syncOriginal();
        $this->assertFalse($entity->hasChanged());

        $entity->value = 'changed2';
        $this->assertTrue($entity->hasChanged());
    }

    public function testHasChangedWithArrayOfObjects(): void
    {
        $entity = new class () extends Entity {
            protected $attributes = [
                'items' => null,
            ];
        };

        $obj1       = new stdClass();
        $obj1->id   = 1;
        $obj1->name = 'First';

        $obj2       = new stdClass();
        $obj2->id   = 2;
        $obj2->name = 'Second';

        $entity->items = [$obj1, $obj2];
        $entity->syncOriginal();

        $this->assertFalse($entity->hasChanged('items'));

        $obj3       = new stdClass();
        $obj3->id   = 1;
        $obj3->name = 'Modified';

        $entity->items = [$obj3, $obj2];

        $this->assertTrue($entity->hasChanged('items'));
    }

    public function testHasChangedWithEmptyArrays(): void
    {
        $entity = new class () extends Entity {
            protected $attributes = [
                'tags' => [],
            ];
        };

        $entity->syncOriginal();

        $this->assertFalse($entity->hasChanged('tags'));

        $entity->tags = ['tag1'];

        $this->assertTrue($entity->hasChanged('tags'));

        $entity->syncOriginal();
        $entity->tags = [];

        $this->assertTrue($entity->hasChanged('tags'));
    }

    public function testHasChangedWithObjectWithToArrayMethod(): void
    {
        $entity = new class () extends Entity {
            protected $attributes = [
                'custom' => null,
            ];
        };

        // Create object with toArray() method
        $obj1 = new class () {
            /**
             * @return array<string, string>
             */
            public function toArray(): array
            {
                return ['key' => 'value1'];
            }
        };

        $entity->custom = $obj1;
        $entity->syncOriginal();

        $this->assertFalse($entity->hasChanged('custom'));

        // Create different object with same class but different toArray() result
        $obj2 = new class () {
            /**
             * @return array<string, string>
             */
            public function toArray(): array
            {
                return ['key' => 'value2'];
            }
        };

        $entity->custom = $obj2;

        $this->assertTrue($entity->hasChanged('custom'));
    }

    public function testHasChangedScalarOptimizationWithNullValues(): void
    {
        $entity = new class () extends Entity {
            protected $attributes = [
                'id'    => 1,
                'name'  => null,
                'email' => null,
            ];
        };

        $entity->syncOriginal();

        $original = $this->getPrivateProperty($entity, 'original');
        $this->assertSame(1, $original['id']);
        $this->assertNull($original['name']);
        $this->assertNull($original['email']);

        $this->assertFalse($entity->hasChanged());

        // Change null to string
        $entity->name = 'John';

        $this->assertTrue($entity->hasChanged());
    }

    public function testHasChangedDetectsNewPropertyAddition(): void
    {
        $entity = new class () extends Entity {
            protected $attributes = [
                'existing' => 'value',
            ];
        };

        $entity->syncOriginal();

        // Add new property
        $entity->newProp = 'new value';

        $this->assertTrue($entity->hasChanged());
        $this->assertTrue($entity->hasChanged('newProp'));
    }

    public function testHasChangedWithBackedEnumString(): void
    {
        $entity = new class () extends Entity {
            protected $attributes = [
                'status' => null,
            ];
        };

        $entity->status = StatusEnum::ACTIVE;
        $entity->syncOriginal();

        $this->assertFalse($entity->hasChanged('status'));

        $entity->status = StatusEnum::PENDING;

        $this->assertTrue($entity->hasChanged('status'));
    }

    public function testHasChangedWithBackedEnumInt(): void
    {
        $entity = new class () extends Entity {
            protected $attributes = [
                'role' => null,
            ];
        };

        $entity->role = RoleEnum::USER;
        $entity->syncOriginal();

        $this->assertFalse($entity->hasChanged('role'));

        $entity->role = RoleEnum::ADMIN;

        $this->assertTrue($entity->hasChanged('role'));
    }

    public function testHasChangedWithUnitEnum(): void
    {
        $entity = new class () extends Entity {
            protected $attributes = [
                'color' => null,
            ];
        };

        $entity->color = ColorEnum::RED;
        $entity->syncOriginal();

        $this->assertFalse($entity->hasChanged('color'));

        $entity->color = ColorEnum::BLUE;

        $this->assertTrue($entity->hasChanged('color'));
    }

    public function testHasChangedDoesNotDetectSameEnum(): void
    {
        $entity = new class () extends Entity {
            protected $attributes = [
                'status' => null,
            ];
        };

        $entity->status = StatusEnum::ACTIVE;
        $entity->syncOriginal();

        $entity->status = StatusEnum::ACTIVE;

        $this->assertFalse($entity->hasChanged('status'));
    }

    public function testSyncOriginalWithEnumValues(): void
    {
        $entity = new class () extends Entity {
            protected $attributes = [
                'status' => StatusEnum::PENDING,
                'role'   => RoleEnum::USER,
                'color'  => ColorEnum::GREEN,
            ];
        };

        $entity->syncOriginal();

        $original = $this->getPrivateProperty($entity, 'original');

        // Enums should be JSON-encoded as objects
        $this->assertIsString($original['status']);
        $this->assertIsString($original['role']);
        $this->assertIsString($original['color']);

        $statusData = json_decode($original['status'], true);
        $this->assertSame(StatusEnum::class, $statusData['__class']);
        $this->assertSame('pending', $statusData['__enum']);

        $roleData = json_decode($original['role'], true);
        $this->assertSame(RoleEnum::class, $roleData['__class']);
        $this->assertSame(1, $roleData['__enum']);

        $colorData = json_decode($original['color'], true);
        $this->assertSame(ColorEnum::class, $colorData['__class']);
        $this->assertSame('GREEN', $colorData['__enum']);
    }

    public function testHasChangedWithDateTimeInterface(): void
    {
        $entity = new class () extends Entity {
            protected $attributes = [
                'created_at' => null,
            ];
        };

        // Test with Time object
        $entity->created_at = Time::parse('2024-01-01 12:00:00', 'UTC');
        $entity->syncOriginal();

        $this->assertFalse($entity->hasChanged('created_at'));

        $entity->created_at = Time::parse('2024-12-31 23:59:59', 'UTC');
        $this->assertTrue($entity->hasChanged('created_at'));

        $entity->syncOriginal();
        $entity->created_at = Time::parse('2024-12-31 23:59:59', 'UTC');
        $this->assertFalse($entity->hasChanged('created_at'));

        // Test timezone difference detection
        $entity->created_at = new DateTime('2024-01-01 12:00:00', new DateTimeZone('UTC'));
        $entity->syncOriginal();
        $entity->created_at = new DateTime('2024-01-01 12:00:00', new DateTimeZone('America/New_York'));
        $this->assertTrue($entity->hasChanged('created_at'));
    }

    public function testHasChangedWithTraversable(): void
    {
        $entity = new class () extends Entity {
            protected $attributes = [
                'items' => null,
            ];
        };

        // Test with ArrayObject
        $entity->items = new ArrayObject(['a', 'b', 'c']);
        $entity->syncOriginal();

        $this->assertFalse($entity->hasChanged('items'));

        $entity->items = new ArrayObject(['a', 'b', 'd']);
        $this->assertTrue($entity->hasChanged('items'));

        $entity->syncOriginal();
        $entity->items = new ArrayObject(['a', 'b', 'd']);
        $this->assertFalse($entity->hasChanged('items'));

        // Test with ArrayIterator
        $entity->items = new ArrayIterator(['x', 'y', 'z']);
        $entity->syncOriginal();
        $entity->items = new ArrayIterator(['x', 'y', 'modified']);
        $this->assertTrue($entity->hasChanged('items'));

        // Test with nested objects inside collection (verifies recursive normalization)
        $obj1       = new stdClass();
        $obj1->name = 'first';

        $obj2       = new stdClass();
        $obj2->name = 'second';

        $entity->items = new ArrayObject([$obj1, $obj2]);
        $entity->syncOriginal();

        $obj3       = new stdClass();
        $obj3->name = 'modified';

        $entity->items = new ArrayObject([$obj3, $obj2]);
        $this->assertTrue($entity->hasChanged('items'));
    }

    public function testHasChangedWithValueObjectsUsingToString(): void
    {
        // Define a value object class
        $emailClass = new class () {
            public static function create(string $email): object
            {
                return new class ($email) {
                    public function __construct(private readonly string $email)
                    {
                    }

                    public function __toString(): string
                    {
                        return $this->email;
                    }
                };
            }
        };

        $entity = new class () extends Entity {
            protected $attributes = [
                'email' => null,
            ];
        };

        $entity->email = $emailClass::create('old@example.com');
        $entity->syncOriginal();

        $this->assertFalse($entity->hasChanged('email'));

        $entity->email = $emailClass::create('new@example.com');
        $this->assertTrue($entity->hasChanged('email'));

        $entity->syncOriginal();
        $entity->email = $emailClass::create('new@example.com');
        $this->assertFalse($entity->hasChanged('email'));
    }
}
