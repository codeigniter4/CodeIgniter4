<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Entity;

use Closure;
use CodeIgniter\Entity\Exceptions\CastException;
use CodeIgniter\HTTP\URI;
use CodeIgniter\I18n\Time;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ReflectionHelper;
use DateTime;
use DateTimeInterface;
use ReflectionException;
use Tests\Support\Entity\Cast\CastBase64;
use Tests\Support\Entity\Cast\CastPassParameters;
use Tests\Support\Entity\Cast\NotExtendsBaseCast;
use Tests\Support\SomeEntity;

/**
 * @internal
 *
 * @group Others
 */
final class EntityTest extends CIUnitTestCase
{
    use ReflectionHelper;

    public function testSetStringToPropertyNamedAttributes()
    {
        $entity = $this->getEntity();

        $entity->attributes = 'attributes';

        $this->assertSame('attributes', $entity->attributes);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues
     */
    public function testSetArrayToPropertyNamedAttributes()
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

    public function testNewGetterSetters()
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

        $entity->setAttributes(['active' => '1']);

        $this->assertTrue($entity->active);

        $entity->setAttributes(['active' => '0']);

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
        $this->assertSame(3.0, $entity->second);

        $entity->second = '3.6';

        $this->assertIsFloat($entity->second);
        $this->assertSame(3.6, $entity->second);
    }

    public function testCastDouble(): void
    {
        $entity = $this->getCastEntity();

        $entity->third = 3;

        $this->assertIsFloat($entity->third);
        $this->assertSame(3.0, $entity->third);

        $entity->third = '3.6';

        $this->assertIsFloat($entity->third);
        $this->assertSame(3.6, $entity->third);
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

        $entity->ninth;
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

    protected function getEntity()
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

            public function setBar($value)
            {
                $this->attributes['bar'] = "bar:{$value}";

                return $this;
            }

            public function getBar()
            {
                return "{$this->attributes['bar']}:bar";
            }

            public function getFakeBar()
            {
                return "{$this->attributes['bar']}:bar";
            }
        };
    }

    protected function getNewSetterGetterEntity()
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

            public function setBar($value)
            {
                $this->bar = $value;

                return $this;
            }

            public function getBar()
            {
                return $this->bar;
            }

            public function _setBar($value)
            {
                $this->attributes['bar'] = "bar:{$value}";

                return $this;
            }

            public function _getBar()
            {
                return "{$this->attributes['bar']}:bar";
            }
        };
    }

    protected function getMappedEntity()
    {
        return new class () extends Entity {
            protected $attributes = [
                'foo'    => null,
                'simple' => null,
            ];
            protected $_original = [
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

            protected function getSimple()
            {
                return $this->attributes['simple'] . ':oo';
            }
        };
    }

    protected function getSwappedEntity()
    {
        return new class () extends Entity {
            protected $attributes = [
                'foo' => 'foo',
                'bar' => 'bar',
            ];
            protected $_original = [
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

    protected function getSimpleSwappedEntity()
    {
        return new class () extends Entity {
            protected $attributes = [
                'foo' => 'foo',
                'bar' => 'bar',
            ];
            protected $_original = [
                'foo' => 'foo',
                'bar' => 'bar',
            ];
            protected $datamap = [
                'bar' => 'foo',
                'foo' => 'bar',
            ];
        };
    }

    protected function getCastEntity($data = null): Entity
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
            protected $_original = [
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

            public function setSeventh($seventh): void
            {
                $this->attributes['seventh'] = $seventh;
            }
        };
    }

    protected function getCastNullableEntity()
    {
        return new class () extends Entity {
            protected $attributes = [
                'string_null'           => null,
                'string_empty'          => null,
                'integer_null'          => null,
                'integer_0'             => null,
                'string_value_not_null' => 'value',
            ];
            protected $_original = [
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

    protected function getCustomCastEntity()
    {
        return new class () extends Entity {
            protected $attributes = [
                'first'  => null,
                'second' => null,
                'third'  => null,
                'fourth' => null,
            ];
            protected $_original = [
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
}
