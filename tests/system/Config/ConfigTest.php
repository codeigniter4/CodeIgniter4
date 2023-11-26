<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Config;

use CodeIgniter\Test\CIUnitTestCase;
use Config\DocTypes;
use stdClass;

/**
 * @internal
 *
 * @group SeparateProcess
 */
final class ConfigTest extends CIUnitTestCase
{
    public function testCreateSingleInstance(): void
    {
        $Config          = Config::get('DocTypes', false);
        $NamespaceConfig = Config::get(DocTypes::class, false);

        $this->assertInstanceOf(DocTypes::class, $Config);
        $this->assertInstanceOf(DocTypes::class, $NamespaceConfig);
    }

    public function testCreateInvalidInstance(): void
    {
        $Config = Config::get('gfnusvjai', false);

        $this->assertNull($Config);
    }

    public function testCreateSharedInstance(): void
    {
        $Config  = Config::get('DocTypes');
        $Config2 = Config::get(DocTypes::class);

        $this->assertSame($Config2, $Config);
    }

    public function testCreateNonConfig(): void
    {
        $Config = Config::get('Constants', false);

        $this->assertNull($Config);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testInjection(): void
    {
        Config::reset();
        Config::injectMock('Banana', new stdClass());
        $this->assertNotNull(Config::get('Banana'));
    }
}
