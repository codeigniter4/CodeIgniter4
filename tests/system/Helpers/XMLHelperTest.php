<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Helpers;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 *
 * @group Others
 */
final class XMLHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        helper('xml');
    }

    public function testConvert(): void
    {
        $original = '<p>Here is a so-so paragraph & an entity (&#123;).</p>';
        $expected = '&lt;p&gt;Here is a so&#45;so paragraph &amp; an entity (&#123;).&lt;/p&gt;';
        $this->assertSame($expected, xml_convert($original));
    }

    public function testConvertProtected(): void
    {
        $original = '<p>Here is a so&so; paragraph & an entity (&#123;).</p>';
        $expected = '&lt;p&gt;Here is a so&so; paragraph &amp; an entity (&#123;).&lt;/p&gt;';
        $this->assertSame($expected, xml_convert($original, true));
    }
}
