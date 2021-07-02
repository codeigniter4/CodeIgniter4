<?php

namespace CodeIgniter\HTTP;

use CodeIgniter\Test\CIUnitTestCase;
use stdClass;

/**
 * @internal
 */
final class HeaderTest extends CIUnitTestCase
{
    public function testHeaderStoresBasics()
    {
        $name  = 'foo';
        $value = 'bar';

        $header = new Header($name, $value);

        $this->assertSame($name, $header->getName());
        $this->assertSame($value, $header->getValue());
    }

    public function testHeaderStoresBasicsWithNull()
    {
        $name  = 'foo';
        $value = null;

        $header = new Header($name, $value);

        $this->assertSame($name, $header->getName());
        $this->assertSame('', $header->getValue());
    }

    //--------------------------------------------------------------------

    public function testHeaderStoresArrayValues()
    {
        $name  = 'foo';
        $value = [
            'bar',
            'baz',
        ];

        $header = new Header($name, $value);

        $this->assertSame($name, $header->getName());
        $this->assertSame($value, $header->getValue());
    }

    //--------------------------------------------------------------------

    public function testHeaderSetters()
    {
        $name  = 'foo';
        $value = [
            'bar',
            'baz',
        ];

        $header = new Header($name);
        $this->assertSame($name, $header->getName());
        $this->assertEmpty($header->getValue());
        $this->assertSame($name . ': ', (string) $header);

        $name = 'foo2';
        $header->setName($name)->setValue($value);
        $this->assertSame($name, $header->getName());
        $this->assertSame($value, $header->getValue());
        $this->assertSame($name . ': bar, baz', (string) $header);
    }

    //--------------------------------------------------------------------

    public function testHeaderAppendsValueSkippedForNull()
    {
        $name     = 'foo';
        $value    = 'bar';
        $expected = 'bar';

        $header = new Header($name, $value);

        $header->appendValue(null);

        $this->assertSame($name, $header->getName());
        $this->assertSame($expected, $header->getValue());
    }

    public function testHeaderConvertsSingleToArray()
    {
        $name  = 'foo';
        $value = 'bar';

        $expected = [
            'bar',
            'baz',
        ];

        $header = new Header($name, $value);

        $header->appendValue('baz');

        $this->assertSame($name, $header->getName());
        $this->assertSame($expected, $header->getValue());
    }

    //--------------------------------------------------------------------

    public function testHeaderPrependsValueSkippedForNull()
    {
        $name     = 'foo';
        $value    = 'bar';
        $expected = 'bar';

        $header = new Header($name, $value);

        $header->prependValue(null);

        $this->assertSame($name, $header->getName());
        $this->assertSame($expected, $header->getValue());
    }

    public function testHeaderPrependsValue()
    {
        $name  = 'foo';
        $value = 'bar';

        $expected = [
            'baz',
            'bar',
        ];

        $header = new Header($name, $value);

        $header->prependValue('baz');

        $this->assertSame($name, $header->getName());
        $this->assertSame($expected, $header->getValue());
    }

    //--------------------------------------------------------------------

    public function testHeaderLineSimple()
    {
        $name  = 'foo';
        $value = [
            'bar',
            'baz',
        ];

        $expected = 'bar, baz';

        $header = new Header($name, $value);

        $this->assertSame($name, $header->getName());
        $this->assertSame($expected, $header->getValueLine());
    }

    public function testHeaderLineValueNotStringOrArray()
    {
        $name  = 'foo';
        $value = new stdClass();

        $expected = '';

        $header = new Header($name, $value);

        $this->assertSame($name, $header->getName());
        $this->assertSame($expected, $header->getValueLine());
    }

    //--------------------------------------------------------------------

    public function testHeaderSetValueWithNullWillMarkAsEmptyString()
    {
        $name     = 'foo';
        $expected = '';

        $header = new Header($name);
        $header->setValue('bar')->setValue(null);

        $this->assertSame($name, $header->getName());
        $this->assertSame($expected, $header->getValueLine());
    }

    public function testHeaderLineWithArrayValues()
    {
        $name = 'foo';

        $expected = 'bar, baz=fuzz';

        $header = new Header($name);

        $header->setValue('bar')->appendValue(['baz' => 'fuzz']);

        $this->assertSame($name, $header->getName());
        $this->assertSame($expected, $header->getValueLine());
    }

    //--------------------------------------------------------------------

    public function testHeaderToStringShowsEntireHeader()
    {
        $name = 'foo';

        $expected = 'foo: bar, baz=fuzz';

        $header = new Header($name);

        $header->setValue('bar')->appendValue(['baz' => 'fuzz']);

        $this->assertSame($expected, (string) $header);
    }
}
