<?php

namespace CodeIgniter\Entity;

use CodeIgniter\Entity\Exceptions\CastException;
use CodeIgniter\HTTP\URI;
use CodeIgniter\I18n\Time;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ReflectionHelper;
use DateTime;
use ReflectionException;
use stdClass;
use Tests\Support\Entity\Cast\CastBase64;
use Tests\Support\Entity\Cast\CastPassParameters;
use Tests\Support\Entity\Cast\NotExtendsBaseCast;
use Tests\Support\SomeEntity;

/**
 * @internal
 */
final class EntityTest extends CIUnitTestCase
{
    use ReflectionHelper;

    public function testSimpleSetAndGet()
    {
        $entity = $this->getEntity();

        $entity->foo = 'to wong';

        $this->assertSame('to wong', $entity->foo);
    }

    public function testGetterSetters()
    {
        $entity = $this->getEntity();

        $entity->bar = 'thanks';

        $this->assertSame('bar:thanks:bar', $entity->bar);
    }

    public function testUnsetUnsetsAttribute()
    {
        $entity = $this->getEntity();

        $this->assertSame('sumfin', $entity->default);

        $entity->default = 'else';

        $this->assertSame('else', $entity->default);

        unset($entity->default);

        $this->assertNull($entity->default);
    }

    public function testIssetWorksLikeTraditionalIsset()
    {
        $entity = $this->getEntity();

        $this->assertFalse(isset($entity->foo));

        $attributes = $this->getPrivateProperty($entity, 'attributes');
        $this->assertFalse(isset($attributes['foo']));
        $this->assertTrue(isset($attributes['default']));
    }

    public function testFill()
    {
        $entity = $this->getEntity();

        $entity->fill([
            'foo' => 123,
            'bar' => 234,
            'baz' => 4556,
        ]);

        $this->assertSame(123, $entity->foo);
        $this->assertSame('bar:234:bar', $entity->bar);
        $this->assertObjectNotHasAttribute('baz', $entity);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1567
     */
    public function testFillMapsEntities()
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

    public function testDataMappingConvertsOriginalName()
    {
        $entity = $this->getMappedEntity();

        $entity->bar = 'made it';

        // Check mapped field
        $this->assertSame('made it', $entity->foo);

        // Should also get from original name
        // since Model's would be looking for the original name
        $this->assertSame('made it', $entity->bar);

        // But it shouldn't actually set a class property for the original name...
        $this->expectException(ReflectionException::class);
        $this->getPrivateProperty($entity, 'bar');
    }

    public function testDataMappingWorksWithCustomSettersAndGetters()
    {
        $entity = $this->getMappedEntity();

        // Will map to "simple"
        $entity->orig = 'first';

        $this->assertSame('oo:first:oo', $entity->simple);

        $entity->simple = 'second';

        $this->assertSame('oo:second:oo', $entity->simple);
    }

    public function testIssetWorksWithMapping()
    {
        $entity = $this->getMappedEntity();

        // maps to 'foo'
        $entity->bar = 'here';

        $attributes = $this->getPrivateProperty($entity, 'attributes');

        $this->assertArrayHasKey('foo', $attributes);
        $this->assertArrayNotHasKey('bar', $attributes);
    }

    public function testUnsetWorksWithMapping()
    {
        $entity = $this->getMappedEntity();

        // maps to 'foo'
        $entity->bar = 'here';

        // doesn't work on original name
        unset($entity->bar);
        $this->assertSame('here', $entity->bar);
        $this->assertSame('here', $entity->foo);

        // does work on mapped field
        unset($entity->foo);
        $this->assertNull($entity->foo);
        $this->assertNull($entity->bar);
    }

    public function testDateMutationFromString()
    {
        $entity     = $this->getEntity();
        $attributes = ['created_at' => '2017-07-15 13:23:34'];
        $this->setPrivateProperty($entity, 'attributes', $attributes);

        $time = $entity->created_at;

        $this->assertInstanceOf(Time::class, $time);
        $this->assertSame('2017-07-15 13:23:34', $time->format('Y-m-d H:i:s'));
    }

    public function testDateMutationFromTimestamp()
    {
        $stamp = time();

        $entity     = $this->getEntity();
        $attributes = ['created_at' => $stamp];
        $this->setPrivateProperty($entity, 'attributes', $attributes);

        $time = $entity->created_at;

        $this->assertInstanceOf(Time::class, $time);
        $this->assertCloseEnoughString(date('Y-m-d H:i:s', $stamp), $time->format('Y-m-d H:i:s'));
    }

    public function testDateMutationFromDatetime()
    {
        $dt         = new DateTime('now');
        $entity     = $this->getEntity();
        $attributes = ['created_at' => $dt];
        $this->setPrivateProperty($entity, 'attributes', $attributes);

        $time = $entity->created_at;

        $this->assertInstanceOf(Time::class, $time);
        $this->assertCloseEnoughString($dt->format('Y-m-d H:i:s'), $time->format('Y-m-d H:i:s'));
    }

    public function testDateMutationFromTime()
    {
        $dt         = Time::now();
        $entity     = $this->getEntity();
        $attributes = ['created_at' => $dt];
        $this->setPrivateProperty($entity, 'attributes', $attributes);

        $time = $entity->created_at;

        $this->assertInstanceOf(Time::class, $time);
        $this->assertCloseEnoughString($dt->format('Y-m-d H:i:s'), $time->format('Y-m-d H:i:s'));
    }

    public function testDateMutationStringToTime()
    {
        $entity = $this->getEntity();

        $entity->created_at = '2017-07-15 13:23:34';

        $time = $this->getPrivateProperty($entity, 'attributes')['created_at'];

        $this->assertInstanceOf(Time::class, $time);
        $this->assertSame('2017-07-15 13:23:34', $time->format('Y-m-d H:i:s'));
    }

    public function testDateMutationTimestampToTime()
    {
        $stamp  = time();
        $entity = $this->getEntity();

        $entity->created_at = $stamp;

        $time = $this->getPrivateProperty($entity, 'attributes')['created_at'];

        $this->assertInstanceOf(Time::class, $time);
        $this->assertCloseEnoughString(date('Y-m-d H:i:s'), $time->format('Y-m-d H:i:s'));
    }

    public function testDateMutationDatetimeToTime()
    {
        $dt     = new DateTime('now');
        $entity = $this->getEntity();

        $entity->created_at = $dt;

        $time = $this->getPrivateProperty($entity, 'attributes')['created_at'];

        $this->assertInstanceOf(Time::class, $time);
        $this->assertCloseEnoughString($dt->format('Y-m-d H:i:s'), $time->format('Y-m-d H:i:s'));
    }

    public function testDateMutationTimeToTime()
    {
        $dt     = Time::now();
        $entity = $this->getEntity();

        $entity->created_at = $dt;

        $time = $this->getPrivateProperty($entity, 'attributes')['created_at'];

        $this->assertInstanceOf(Time::class, $time);
        $this->assertCloseEnoughString($dt->format('Y-m-d H:i:s'), $time->format('Y-m-d H:i:s'));
    }

    public function testCastInteger()
    {
        $entity = $this->getCastEntity();

        $entity->first = 3.1;
        $this->assertIsInt($entity->first);
        $this->assertSame(3, $entity->first);

        $entity->first = 3.6;
        $this->assertSame(3, $entity->first);
    }

    public function testCastFloat()
    {
        $entity = $this->getCastEntity();

        $entity->second = 3;
        $this->assertIsFloat($entity->second);
        $this->assertSame(3.0, $entity->second);

        $entity->second = '3.6';
        $this->assertIsFloat($entity->second);
        $this->assertSame(3.6, $entity->second);
    }

    public function testCastDouble()
    {
        $entity = $this->getCastEntity();

        $entity->third = 3;
        $this->assertIsFloat($entity->third);
        $this->assertSame(3.0, $entity->third);

        $entity->third = '3.6';
        $this->assertIsFloat($entity->third);
        $this->assertSame(3.6, $entity->third);
    }

    public function testCastString()
    {
        $entity = $this->getCastEntity();

        $entity->fourth = 3.1415;
        $this->assertIsString($entity->fourth);
        $this->assertSame('3.1415', $entity->fourth);
    }

    public function testCastBoolean()
    {
        $entity = $this->getCastEntity();

        $entity->fifth = 1;
        $this->assertIsBool($entity->fifth);
        $this->assertTrue($entity->fifth);

        $entity->fifth = 0;
        $this->assertIsBool($entity->fifth);
        $this->assertFalse($entity->fifth);
    }

    public function testCastCSV()
    {
        $entity = $this->getCastEntity();

        $data = ['foo', 'bar', 'bam'];

        $entity->twelfth = $data;

        $result = $entity->toRawArray();
        $this->assertIsString($result['twelfth']);
        $this->assertSame('foo,bar,bam', $result['twelfth']);

        $this->assertIsArray($entity->twelfth);
        $this->assertSame($data, $entity->twelfth);
    }

    public function testCastObject()
    {
        $entity = $this->getCastEntity();

        $data = ['foo' => 'bar'];

        $entity->sixth = $data;
        $this->assertIsObject($entity->sixth);
        $this->assertInstanceOf(stdClass::class, $entity->sixth);
        $this->assertSame($data, (array) $entity->sixth);
    }

    public function testCastDateTime()
    {
        $entity = $this->getCastEntity();

        $entity->eighth = 'March 12, 2017';
        $this->assertInstanceOf('DateTime', $entity->eighth);
        $this->assertSame('2017-03-12', $entity->eighth->format('Y-m-d'));
    }

    public function testCastTimestamp()
    {
        $entity = $this->getCastEntity();

        $date = 'March 12, 2017';

        $entity->ninth = $date;
        $this->assertIsInt($entity->ninth);
        $this->assertSame(strtotime($date), $entity->ninth);
    }

    public function testCastTimestampException()
    {
        $entity = $this->getCastEntity();

        $entity->ninth = 'some string';

        $this->expectException(CastException::class);
        $this->expectErrorMessage('Type casting "timestamp" expects a correct timestamp.');

        $entity->ninth;
    }

    public function testCastArray()
    {
        $entity = $this->getCastEntity();

        $entity->seventh = ['foo' => 'bar'];

        $check = $this->getPrivateProperty($entity, 'attributes')['seventh'];
        $this->assertSame(serialize(['foo' => 'bar']), $check);

        $this->assertSame(['foo' => 'bar'], $entity->seventh);
    }

    public function testCastArrayByStringSerialize()
    {
        $entity = $this->getCastEntity();

        $entity->seventh = 'foobar';

        // Should be a serialized string now...
        $check = $this->getPrivateProperty($entity, 'attributes')['seventh'];
        $this->assertSame(serialize('foobar'), $check);

        $this->assertSame(['foobar'], $entity->seventh);
    }

    public function testCastArrayByArraySerialize()
    {
        $entity = $this->getCastEntity();

        $entity->seventh = ['foo' => 'bar'];

        // Should be a serialized string now...
        $check = $this->getPrivateProperty($entity, 'attributes')['seventh'];
        $this->assertSame(serialize(['foo' => 'bar']), $check);

        $this->assertSame(['foo' => 'bar'], $entity->seventh);
    }

    public function testCastArrayByFill()
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

    public function testCastArrayByConstructor()
    {
        $data = ['seventh' => [1, 2, 3]];

        $entity = $this->getCastEntity($data);

        // Check if serialiazed
        $check = $this->getPrivateProperty($entity, 'attributes')['seventh'];
        $this->assertSame(serialize([1, 2, 3]), $check);

        // Check if unserialized
        $this->assertSame([1, 2, 3], $entity->seventh);
    }

    public function testCastNullable()
    {
        $entity = $this->getCastNullableEntity();

        $this->assertNull($entity->string_null);
        $this->assertSame('', $entity->string_empty);
        $this->assertNull($entity->integer_null);
        $this->assertSame(0, $entity->integer_0);
        $this->assertSame('value', $entity->string_value_not_null);
    }

    public function testCastURI()
    {
        $entity = $this->getCastEntity();

        $data = 'https://codeigniter.com/banana';

        $entity->thirteenth = $data;
        $this->assertInstanceOf(URI::class, $entity->thirteenth);
        $this->assertSame($data, (string) $entity->thirteenth);
        $this->assertSame('/banana', $entity->thirteenth->getPath());
    }

    public function testURICastURI()
    {
        $entity = $this->getCastEntity();

        $data = 'https://codeigniter.com/banana';

        $entity->thirteenth = new URI($data);
        $this->assertInstanceOf(URI::class, $entity->thirteenth);
        $this->assertSame($data, (string) $entity->thirteenth);
        $this->assertSame('/banana', $entity->thirteenth->getPath());
    }

    public function testCastAsJSON()
    {
        $entity = $this->getCastEntity();

        $entity->tenth = ['foo' => 'bar'];

        // Should be a JSON-encoded string now...
        $check = $this->getPrivateProperty($entity, 'attributes')['tenth'];
        $this->assertSame('{"foo":"bar"}', $check);

        $this->assertInstanceOf(stdClass::class, $entity->tenth);
        $this->assertSame(['foo' => 'bar'], (array) $entity->tenth);
    }

    public function testCastAsJSONArray()
    {
        $entity = $this->getCastEntity();

        $data = ['Sun', 'Mon', 'Tue'];

        $entity->eleventh = $data;

        // Should be a JSON-encoded string now...
        $check = $this->getPrivateProperty($entity, 'attributes')['eleventh'];
        $this->assertSame('["Sun","Mon","Tue"]', $check);

        $this->assertSame($data, $entity->eleventh);
    }

    public function testCastAsJsonByFill()
    {
        $entity = $this->getCastEntity();
        $data   = ['eleventh' => [1, 2, 3]];

        $entity->fill($data);

        // Check if serialiazed
        $check = $this->getPrivateProperty($entity, 'attributes')['eleventh'];
        $this->assertSame(json_encode([1, 2, 3]), $check);

        // Check if unserialized
        $this->assertSame([1, 2, 3], $entity->eleventh);
    }

    public function testCastAsJsonByConstructor()
    {
        $data   = ['eleventh' => [1, 2, 3]];
        $entity = $this->getCastEntity($data);

        // Check if serialiazed
        $check = $this->getPrivateProperty($entity, 'attributes')['eleventh'];
        $this->assertSame(json_encode([1, 2, 3]), $check);

        // Check if unserialized
        $this->assertSame([1, 2, 3], $entity->eleventh);
    }

    public function testCastAsJSONErrorDepth()
    {
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

        $current = $value;
        $this->expectException(CastException::class);
        $this->expectExceptionMessage('Maximum stack depth exceeded');

        $entity->tenth = $array;
        $this->getPrivateProperty($entity, 'tenth');
    }

    public function testCastAsJSONErrorUTF8()
    {
        $entity = $this->getCastEntity();

        $this->expectException(CastException::class);
        $this->expectExceptionMessage('Malformed UTF-8 characters, possibly incorrectly encoded');

        $entity->tenth = "\xB1\x31";
        $this->getPrivateProperty($entity, 'tenth');
    }

    public function testCastAsJSONSyntaxError()
    {
        $entity = new Entity();

        $method = $this->getPrivateMethodInvoker($entity, 'castAsJson');

        $this->expectException(CastException::class);
        $this->expectExceptionMessage('Syntax error, malformed JSON');

        $method('{ this is bad string', true);
    }

    public function testCastAsJSONAnotherErrorDepth()
    {
        $entity = new Entity();

        $method = $this->getPrivateMethodInvoker($entity, 'castAsJson');

        $this->expectException(CastException::class);
        $this->expectExceptionMessage('Maximum stack depth exceeded');

        $string = '{' . str_repeat('"test":{', 513) . '"test":"value"' . str_repeat('}', 513) . '}';

        $method($string, true);
    }

    public function testCastAsJSONControlCharCheck()
    {
        $entity = new Entity();
        $method = $this->getPrivateMethodInvoker($entity, 'castAsJson');

        $this->expectException(CastException::class);
        $this->expectExceptionMessage('Unexpected control character found');

        $string = "{\n\t\"property1\": \"The quick brown fox\njumps over the lazy dog\",\n\t\"property2\":\"value2\"\n}";

        $method($string, true);
    }

    public function testCastAsJSONStateMismatch()
    {
        $entity = new Entity();
        $method = $this->getPrivateMethodInvoker($entity, 'castAsJson');

        $this->expectException(CastException::class);
        $this->expectExceptionMessage('Underflow or the modes mismatch');

        $string = '[{"name":"jack","product_id":"1234"]';

        $method($string, true);
    }

    public function testCastSetter()
    {
        $string = '321 String with numbers 123';
        $entity = $this->getCastEntity();

        $entity->first = $string;

        $entity->cast(false);
        $this->assertIsString($entity->first);
        $this->assertSame($string, $entity->first);

        $entity->cast(true);
        $this->assertIsInt($entity->first);
        $this->assertSame((int) $string, $entity->first);
    }

    public function testCastGetter()
    {
        $entity = new Entity();

        $this->assertIsBool($entity->cast());
    }

    public function testCustomCast()
    {
        $entity = $this->getCustomCastEntity();

        $entity->first = 'base 64';

        $fieldValue = $this->getPrivateProperty($entity, 'attributes')['first'];

        $this->assertSame(base64_encode('base 64'), $fieldValue);

        $this->assertSame('base 64', $entity->first);
    }

    public function testCustomCastException()
    {
        $entity = $this->getCustomCastEntity();

        $this->expectException(CastException::class);
        $this->expectErrorMessage('The "Tests\Support\Entity\Cast\NotExtendsBaseCast" class must inherit the "CodeIgniter\Entity\Cast\BaseCast" class');
        $entity->second = 'throw Exception';
    }

    public function testCustomCastParams()
    {
        $entity = $this->getCustomCastEntity();

        $entity->third = 'value';

        $this->assertSame('value:["param1","param2","param3"]', $entity->third);

        $entity->fourth = 'test_nullable_type';
        $this->assertSame('test_nullable_type:["nullable"]', $entity->fourth);
    }

    public function testAsArray()
    {
        $entity = $this->getEntity();
        $result = $entity->toArray();

        $this->assertSame($result, [
            'foo'       => null,
            'bar'       => ':bar',
            'default'   => 'sumfin',
            'createdAt' => null,
        ]);
    }

    public function testAsArrayRecursive()
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

    public function testAsArrayMapped()
    {
        $entity = $this->getMappedEntity();
        $result = $entity->toArray();

        $this->assertSame($result, [
            'bar'  => null,
            'orig' => ':oo',
        ]);
    }

    public function testAsArraySwapped()
    {
        $entity = $this->getSwappedEntity();
        $result = $entity->toArray();

        $this->assertSame($result, [
            'bar'          => 'foo',
            'foo'          => 'bar',
            'original_bar' => 'bar',
        ]);
    }

    public function testToArraySkipAttributesWithUnderscoreInFirstCharacter()
    {
        $entity                   = new class() extends Entity {
            protected $attributes = [
                '_foo' => null,
                'bar'  => null,
            ];
        };

        $result = $entity->toArray();

        $this->assertSame($result, [
            'bar' => null,
        ]);
    }

    public function testAsArrayOnlyChanged()
    {
        $entity = $this->getEntity();

        $entity->bar = 'foo';

        $result = $entity->toArray(true);

        $this->assertSame($result, [
            'bar' => 'bar:foo:bar',
        ]);
    }

    public function testToRawArray()
    {
        $entity = $this->getEntity();

        $result = $entity->toRawArray();

        $this->assertSame($result, [
            'foo'        => null,
            'bar'        => null,
            'default'    => 'sumfin',
            'created_at' => null,
        ]);
    }

    public function testToRawArrayRecursive()
    {
        $entity         = $this->getEntity();
        $entity->entity = $this->getEntity();

        $result = $entity->toRawArray(false, true);

        $this->assertSame($result, [
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
        ]);
    }

    public function testToRawArrayOnlyChanged()
    {
        $entity = $this->getEntity();

        $entity->bar = 'foo';

        $result = $entity->toRawArray(true);

        $this->assertSame($result, [
            'bar' => 'bar:foo',
        ]);
    }

    public function testFilledConstruction()
    {
        $data = [
            'foo' => 'bar',
            'bar' => 'baz',
        ];

        $something = new SomeEntity($data);
        $this->assertSame('bar', $something->foo);
        $this->assertSame('baz', $something->bar);
    }

    public function testChangedArray()
    {
        $data = ['bar' => 'baz'];

        $something = new SomeEntity($data);
        $this->assertSame($data, $something->toArray(true));

        $something->magic = 'rockin';

        $this->assertSame([
            'foo'   => null,
            'bar'   => 'baz',
            'magic' => 'rockin',
        ], $something->toArray(false));
    }

    public function testHasChangedNotExists()
    {
        $entity = new SomeEntity();

        $this->assertFalse($entity->hasChanged('foo'));
    }

    public function testHasChangedNewElement()
    {
        $entity = new SomeEntity();

        $entity->foo = 'bar';

        $this->assertTrue($entity->hasChanged('foo'));
    }

    public function testHasChangedNoChange()
    {
        $entity = $this->getEntity();

        $this->assertFalse($entity->hasChanged('default'));
    }

    public function testHasChangedWholeEntity()
    {
        $entity = $this->getEntity();

        $entity->foo = 'bar';

        $this->assertTrue($entity->hasChanged());
    }

    public function testHasChangedKeyNotExists()
    {
        $entity = $this->getEntity();
        $this->assertFalse($entity->hasChanged('xxx'));
    }

    public function testIssetKeyMap()
    {
        $entity = $this->getEntity();

        $entity->created_at = '12345678';
        $this->assertTrue(isset($entity->createdAt));

        $entity->bar = 'foo';
        $this->assertTrue(isset($entity->FakeBar));
    }

    public function testJsonSerializableEntity()
    {
        $entity = $this->getEntity();
        $entity->setBar('foo');
        $this->assertSame(json_encode($entity->toArray()), json_encode($entity));
    }

    protected function getEntity()
    {
        return new class() extends Entity {
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

    protected function getMappedEntity()
    {
        return new class() extends Entity {
            protected $attributes = [
                'foo'    => null,
                'simple' => null,
            ];

            protected $_original = [
                'foo'    => null,
                'simple' => null,
            ];

            // 'bar' is db column, 'foo' is internal representation
            protected $datamap = [
                'bar'  => 'foo',
                'orig' => 'simple',
            ];

            protected function setSimple(string $val)
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
        return new class() extends Entity {
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

    protected function getCastEntity($data = null): Entity
    {
        return new class($data) extends Entity {
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

            public function setSeventh($seventh)
            {
                $this->attributes['seventh'] = $seventh;
            }
        };
    }

    protected function getCastNullableEntity()
    {
        return new class() extends Entity {
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
        return new class() extends Entity {
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
