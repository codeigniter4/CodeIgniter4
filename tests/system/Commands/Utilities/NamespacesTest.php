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

    protected function getBuffer()
    {
        return $this->getStreamFilterBuffer();
    }

    public function testNamespacesCommandCodeIgniterOnly()
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

    public function testNamespacesCommandAllNamespaces()
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
