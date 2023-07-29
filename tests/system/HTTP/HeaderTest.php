<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\HTTP;

use CodeIgniter\Test\CIUnitTestCase;
use Error;
use stdClass;

/**
 * @internal
 *
 * @group Others
 */
final class HeaderTest extends CIUnitTestCase
{
    public function testHeaderStoresBasics(): void
    {
        $name  = 'foo';
        $value = 'bar';

        $header = new Header($name, $value);

        $this->assertSame($name, $header->getName());
        $this->assertSame($value, $header->getValue());
    }

    public function testHeaderStoresBasicsWithNull(): void
    {
        $name  = 'foo';
        $value = null;

        $header = new Header($name, $value);

        $this->assertSame($name, $header->getName());
        $this->assertSame('', $header->getValue());
    }

    public function testHeaderStoresBasicWithInt(): void
    {
        $name  = 'foo';
        $value = 123;

        $header = new Header($name, $value);

        $this->assertSame($name, $header->getName());
        $this->assertSame((string) $value, $header->getValue());
    }

    public function testHeaderStoresBasicWithObject(): void
    {
        $this->expectException(Error::class);
        $this->expectExceptionMessage('Object of class stdClass could not be converted to string');

        $name  = 'foo';
        $value = new stdClass();

        new Header($name, $value);
    }

    public function testHeaderStoresArrayValues(): void
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

    public function testHeaderStoresArrayKeyValue(): void
    {
        $name  = 'foo';
        $value = [
            'key' => 'val',
        ];

        $header = new Header($name, $value);

        $this->assertSame($name, $header->getName());
        $this->assertSame($value, $header->getValue());
        $this->assertSame('key=val', $header->getValueLine());
    }

    public function testHeaderSetters(): void
    {
        $name  = 'foo';
        $value = [
            'bar',
            123,
        ];

        $header = new Header($name);
        $this->assertSame($name, $header->getName());
        $this->assertEmpty($header->getValue());
        $this->assertSame($name . ': ', (string) $header);

        $name = 'foo2';
        $header->setName($name)->setValue($value);
        $this->assertSame($name, $header->getName());
        $this->assertSame($value, $header->getValue());
        $this->assertSame($name . ': bar, 123', (string) $header);
    }

    public function testHeaderAppendsValueSkippedForNull(): void
    {
        $name     = 'foo';
        $value    = 'bar';
        $expected = 'bar';

        $header = new Header($name, $value);

        $header->appendValue(null);

        $this->assertSame($name, $header->getName());
        $this->assertSame($expected, $header->getValue());
    }

    public function testHeaderConvertsSingleToArray(): void
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

    public function testHeaderPrependsValueSkippedForNull(): void
    {
        $name     = 'foo';
        $value    = 'bar';
        $expected = 'bar';

        $header = new Header($name, $value);

        $header->prependValue(null);

        $this->assertSame($name, $header->getName());
        $this->assertSame($expected, $header->getValue());
    }

    public function testHeaderPrependsValue(): void
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

    public function testHeaderLineSimple(): void
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

    public function testHeaderSetValueWithNullWillMarkAsEmptyString(): void
    {
        $name     = 'foo';
        $expected = '';

        $header = new Header($name);
        $header->setValue('bar')->setValue(null);

        $this->assertSame($name, $header->getName());
        $this->assertSame($expected, $header->getValueLine());
    }

    public function testHeaderLineWithArrayValues(): void
    {
        $name = 'foo';

        $expected = 'bar, baz=fuzz';

        $header = new Header($name);

        $header->setValue('bar')->appendValue(['baz' => 'fuzz']);

        $this->assertSame($name, $header->getName());
        $this->assertSame($expected, $header->getValueLine());
    }

    public function testHeaderToStringShowsEntireHeader(): void
    {
        $name = 'foo';

        $expected = 'foo: bar, baz=fuzz';

        $header = new Header($name);

        $header->setValue('bar')->appendValue(['baz' => 'fuzz']);

        $this->assertSame($expected, (string) $header);
    }
}
