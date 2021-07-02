<?php

namespace CodeIgniter\Format;

use CodeIgniter\Test\CIUnitTestCase;
use DOMDocument;

/**
 * @internal
 */
final class XMLFormatterTest extends CIUnitTestCase
{
    protected $xmlFormatter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->xmlFormatter = new XMLFormatter();
    }

    public function testBasicXML()
    {
        $data = [
            'foo' => 'bar',
        ];

        $expected = <<<'EOH'
            <?xml version="1.0"?>
            <response><foo>bar</foo></response>

            EOH;

        $this->assertSame($expected, $this->xmlFormatter->format($data));
    }

    public function testFormatXMLWithMultilevelArray()
    {
        $data = [
            'foo' => ['bar'],
        ];

        $expected = <<<'EOH'
            <?xml version="1.0"?>
            <response><foo><item0>bar</item0></foo></response>

            EOH;

        $this->assertSame($expected, $this->xmlFormatter->format($data));
    }

    public function testFormatXMLWithMultilevelArrayAndNumericKey()
    {
        $data = [
            ['foo'],
        ];

        $expected = <<<'EOH'
            <?xml version="1.0"?>
            <response><item0><item0>foo</item0></item0></response>

            EOH;

        $this->assertSame($expected, $this->xmlFormatter->format($data));
    }

    public function testStringFormatting()
    {
        $data     = ['Something'];
        $expected = <<<'EOH'
            <?xml version="1.0"?>
            <response><item0>Something</item0></response>

            EOH;

        $this->assertSame($expected, $this->xmlFormatter->format($data));
    }

    public function testValidatingXmlTags()
    {
        $data = [
            'BBB096630BD' => 'foo',
            '096630FR'    => 'bar',
        ];
        $expected = <<<'EOH'
            <?xml version="1.0"?>
            <response><BBB096630BD>foo</BBB096630BD><item096630FR>bar</item096630FR></response>

            EOH;

        $this->assertSame($expected, $this->xmlFormatter->format($data));
    }

    /**
     * @param string $expected
     * @param array  $input
     *
     * @dataProvider invalidTagsProvider
     */
    public function testValidatingInvalidTags(string $expected, array $input)
    {
        $expectedXML = <<<EOH
            <?xml version="1.0"?>
            <response><{$expected}>bar</{$expected}></response>

            EOH;

        $this->assertSame($expectedXML, $this->xmlFormatter->format($input));
    }

    public function invalidTagsProvider()
    {
        return [
            [
                'foo',
                [' foo ' => 'bar'],
            ],
            [
                'foobar',
                ['foo:bar' => 'bar'],
            ],
            [
                'foobar',
                ['foo bar' => 'bar'],
            ],
            [
                'itemxmltest',
                ['xmltest' => 'bar'],
            ],
            [
                'itemXMLtest',
                ['XMLtest' => 'bar'],
            ],
            [
                'itemXmltest',
                ['Xmltest' => 'bar'],
            ],
        ];
    }

    public function testDeepNestedArrayToXml()
    {
        $data = [
            'data' => [
                'master' => [
                    'name'       => 'Foo',
                    'email'      => 'foo@bar.com',
                    'dependents' => [],
                ],
                'vote' => [
                    'list' => [],
                ],
                'user' => [
                    'account' => [
                        'demo' => [
                            'info' => [
                                'is_banned'     => 'true',
                                'last_login'    => '2020-08-31',
                                'last_login_ip' => '127.0.0.1',
                            ],
                        ],
                    ],
                ],
                'xml' => [
                    'xml_version'  => '1.0',
                    'xml_encoding' => 'utf-8',
                ],
                [
                    'misc' => 'miscellaneous',
                ],
                [
                    'misc_data' => 'miscellaneous data',
                ],
            ],
        ];

        // do not change to tabs!!
        $expectedXML = <<<'EOF'
            <?xml version="1.0"?>
            <response>
              <data>
                <master>
                  <name>Foo</name>
                  <email>foo@bar.com</email>
                  <dependents/>
                </master>
                <vote>
                  <list/>
                </vote>
                <user>
                  <account>
                    <demo>
                      <info>
                        <is_banned>true</is_banned>
                        <last_login>2020-08-31</last_login>
                        <last_login_ip>127.0.0.1</last_login_ip>
                      </info>
                    </demo>
                  </account>
                </user>
                <itemxml>
                  <itemxml_version>1.0</itemxml_version>
                  <itemxml_encoding>utf-8</itemxml_encoding>
                </itemxml>
                <item0>
                  <misc>miscellaneous</misc>
                </item0>
                <item1>
                  <misc_data>miscellaneous data</misc_data>
                </item1>
              </data>
            </response>

            EOF;

        $dom                     = new DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput       = true;
        $dom->loadXML($this->xmlFormatter->format($data));

        $this->assertSame($expectedXML, $dom->saveXML());
    }
}
