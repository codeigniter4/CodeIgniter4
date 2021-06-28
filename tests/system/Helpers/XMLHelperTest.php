<?php

namespace CodeIgniter\Helpers;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class XMLHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        helper('xml');
    }

    // --------------------------------------------------------------------

    public function testConvert()
    {
        $original = '<p>Here is a so-so paragraph & an entity (&#123;).</p>';
        $expected = '&lt;p&gt;Here is a so&#45;so paragraph &amp; an entity (&#123;).&lt;/p&gt;';
        $this->assertSame($expected, xml_convert($original));
    }

    // --------------------------------------------------------------------
    public function testConvertProtected()
    {
        $original = '<p>Here is a so&so; paragraph & an entity (&#123;).</p>';
        $expected = '&lt;p&gt;Here is a so&so; paragraph &amp; an entity (&#123;).&lt;/p&gt;';
        $this->assertSame($expected, xml_convert($original, true));
    }
}
