<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Format;

use CodeIgniter\Test\CIUnitTestCase;
use RuntimeException;

/**
 * @internal
 */
final class JSONFormatterTest extends CIUnitTestCase
{
    private $jsonFormatter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jsonFormatter = new JSONFormatter();
    }

    public function testBasicJSON()
    {
        $data = [
            'foo' => 'bar',
        ];

        $expected = '{
    "foo": "bar"
}';

        $this->assertSame($expected, $this->jsonFormatter->format($data));
    }

    public function testUnicodeOutput()
    {
        $data = [
            'foo' => 'База данни грешка',
        ];

        $expected = '{
    "foo": "База данни грешка"
}';

        $this->assertSame($expected, $this->jsonFormatter->format($data));
    }

    public function testKeepsURLs()
    {
        $data = [
            'foo' => 'https://www.example.com/foo/bar',
        ];

        $expected = '{
    "foo": "https://www.example.com/foo/bar"
}';

        $this->assertSame($expected, $this->jsonFormatter->format($data));
    }

    public function testJSONError()
    {
        $this->expectException(RuntimeException::class);

        $data     = ["\xB1\x31"];
        $expected = 'Boom';
        $this->assertSame($expected, $this->jsonFormatter->format($data));
    }
}
