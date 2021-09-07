<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Debug;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * @internal
 */
final class ExceptionsTest extends CIUnitTestCase
{
    public function testNew()
    {
        $actual = new Exceptions(new \Config\Exceptions(), Services::request(), Services::response());
        $this->assertInstanceOf(Exceptions::class, $actual);
    }

    /**
     * @dataProvider dirtyPathsProvider
     *
     * @param mixed $file
     * @param mixed $expected
     */
    public function testCleanPaths($file, $expected)
    {
        $this->assertSame($expected, Exceptions::cleanPath($file));
    }

    public function dirtyPathsProvider()
    {
        $ds = DIRECTORY_SEPARATOR;

        return [
            [
                APPPATH . 'Config' . $ds . 'App.php',
                'APPPATH' . $ds . 'Config' . $ds . 'App.php',
            ],
            [
                SYSTEMPATH . 'CodeIgniter.php',
                'SYSTEMPATH' . $ds . 'CodeIgniter.php',
            ],
            [
                VENDORPATH . 'autoload.php',
                'VENDORPATH' . $ds . 'autoload.php',
            ],
            [
                FCPATH . 'index.php',
                'FCPATH' . $ds . 'index.php',
            ],
        ];
    }
}
