<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Commands\Utilities;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;

/**
 * @internal
 *
 * @group Others
 */
final class NamespacesTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    protected function setUp(): void
    {
        $this->resetServices();

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->resetServices();
    }

    /**
     * @see https://regex101.com/r/l3lHfR/1
     */
    protected function getBuffer()
    {
        return preg_replace_callback('/(\|\s*[^|]+\s*\|\s*)(.*?)(\s*\|\s*[^|]+\s*\|)/', static function (array $matches) {
            $matches[2] = str_replace(DIRECTORY_SEPARATOR, '/', $matches[2]);

            return $matches[1] . $matches[2] . $matches[3];
        }, str_replace(PHP_EOL, "\n", $this->getStreamFilterBuffer()));
    }

    public function testNamespacesCommandCodeIgniterOnly(): void
    {
        command('namespaces -c');

        $expected = <<<'EOL'
            +---------------+-------------------------+--------+
            | Namespace     | Path                    | Found? |
            +---------------+-------------------------+--------+
            | CodeIgniter   | ROOTPATH/system         | Yes    |
            | App           | ROOTPATH/app            | Yes    |
            | Config        | APPPATH/Config          | Yes    |
            | Tests\Support | ROOTPATH/tests/_support | Yes    |
            +---------------+-------------------------+--------+
            EOL;

        $this->assertStringContainsString($expected, $this->getBuffer());
    }

    public function testNamespacesCommandAllNamespaces(): void
    {
        command('namespaces');

        $this->assertStringContainsString(
            '|CodeIgniter|ROOTPATH/system|Yes|',
            str_replace(' ', '', $this->getBuffer())
        );
        $this->assertStringContainsString(
            '|App|ROOTPATH/app|Yes|',
            str_replace(' ', '', $this->getBuffer())
        );
        $this->assertStringContainsString(
            '|Config|APPPATH/Config|Yes|',
            str_replace(' ', '', $this->getBuffer())
        );
    }
}
