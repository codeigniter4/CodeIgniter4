<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Filters;

use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockAppConfig;
use RuntimeException;

/**
 * @internal
 */
final class InvalidCharsTest extends CIUnitTestCase
{
    /**
     * @var InvalidChars
     */
    private $invalidChars;

    /**
     * @var IncomingRequest
     */
    private $request;

    protected function setUp(): void
    {
        parent::setUp();

        $_GET    = [];
        $_POST   = [];
        $_COOKIE = [];

        $this->request      = $this->createRequest();
        $this->invalidChars = new InvalidChars();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $_GET    = [];
        $_POST   = [];
        $_COOKIE = [];
    }

    private function createRequest(): IncomingRequest
    {
        $config    = new MockAppConfig();
        $uri       = new URI();
        $userAgent = new UserAgent();
        $request   = $this->getMockBuilder(IncomingRequest::class)
            ->setConstructorArgs([$config, $uri, null, $userAgent])
            ->onlyMethods(['isCLI'])
            ->getMock();
        $request->method('isCLI')->willReturn(false);

        return $request;
    }

    public function testBeforeDoNothingWhenCLIRequest()
    {
        $cliRequest = new CLIRequest(new MockAppConfig());

        $ret = $this->invalidChars->before($cliRequest);

        $this->assertNull($ret);
    }

    public function testBeforeValidString()
    {
        $_POST['val'] = [
            'valid string',
        ];
        $_COOKIE['val'] = 'valid string';

        $ret = $this->invalidChars->before($this->request);

        $this->assertNull($ret);
    }

    public function testBeforeInvalidUTF8StringCausesException()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid UTF-8 characters in post:');

        $sjisString   = mb_convert_encoding('SJISの文字列です。', 'SJIS');
        $_POST['val'] = [
            'valid string',
            $sjisString,
        ];

        $this->invalidChars->before($this->request);
    }

    public function testBeforeInvalidControllCharCausesException()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid Control characters in cookie:');

        $stringWithNullChar = "String contains null char and line break.\0\n";
        $_COOKIE['val']     = $stringWithNullChar;

        $this->invalidChars->before($this->request);
    }

    /**
     * @dataProvider stringWithLineBreakAndTabProvider
     *
     * @param string $input
     */
    public function testCheckControlStringWithLineBreakAndTabReturnsTheString($input)
    {
        $_GET['val'] = $input;

        $ret = $this->invalidChars->before($this->request);

        $this->assertNull($ret);
    }

    public function stringWithLineBreakAndTabProvider()
    {
        return [
            ["String contains \n line break."],
            ["String contains \r line break."],
            ["String contains \r\n line break."],
            ["String contains \t tab."],
            ["String contains \t and \r line \n break."],
        ];
    }

    /**
     * @dataProvider stringWithControlCharsProvider
     *
     * @param string $input
     */
    public function testCheckControlStringWithControlCharsCausesException($input)
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid Control characters in get:');

        $_GET['val'] = $input;

        $ret = $this->invalidChars->before($this->request);

        $this->assertNull($ret);
    }

    public function stringWithControlCharsProvider()
    {
        return [
            ["String contains null char.\0"],
            ["String contains null char and line break.\0\n"],
        ];
    }
}
