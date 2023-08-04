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
use CodeIgniter\Security\Exceptions\SecurityException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockAppConfig;

/**
 * @internal
 *
 * @group Others
 */
final class InvalidCharsTest extends CIUnitTestCase
{
    private InvalidChars $invalidChars;
    private IncomingRequest $request;

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

    /**
     * @doesNotPerformAssertions
     */
    public function testBeforeDoNothingWhenCLIRequest(): void
    {
        $cliRequest = new CLIRequest(new MockAppConfig());

        $this->invalidChars->before($cliRequest);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testBeforeValidString(): void
    {
        $_POST['val'] = [
            'valid string',
        ];
        $_COOKIE['val'] = 'valid string';

        $this->invalidChars->before($this->request);
    }

    public function testBeforeInvalidUTF8StringCausesException(): void
    {
        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('Invalid UTF-8 characters in post:');

        $sjisString   = mb_convert_encoding('SJISの文字列です。', 'SJIS');
        $_POST['val'] = [
            'valid string',
            $sjisString,
        ];

        $this->invalidChars->before($this->request);
    }

    public function testBeforeInvalidControlCharCausesException(): void
    {
        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('Invalid Control characters in cookie:');

        $stringWithNullChar = "String contains null char and line break.\0\n";
        $_COOKIE['val']     = $stringWithNullChar;

        $this->invalidChars->before($this->request);
    }

    /**
     * @doesNotPerformAssertions
     *
     * @dataProvider provideCheckControlStringWithLineBreakAndTabReturnsTheString
     */
    public function testCheckControlStringWithLineBreakAndTabReturnsTheString(string $input): void
    {
        $_GET['val'] = $input;

        $this->invalidChars->before($this->request);
    }

    public static function provideCheckControlStringWithLineBreakAndTabReturnsTheString(): iterable
    {
        yield from [
            ["String contains \n line break."],
            ["String contains \r line break."],
            ["String contains \r\n line break."],
            ["String contains \t tab."],
            ["String contains \t and \r line \n break."],
        ];
    }

    /**
     * @dataProvider provideCheckControlStringWithControlCharsCausesException
     */
    public function testCheckControlStringWithControlCharsCausesException(string $input): void
    {
        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('Invalid Control characters in get:');

        $_GET['val'] = $input;

        $this->invalidChars->before($this->request);
    }

    public static function provideCheckControlStringWithControlCharsCausesException(): iterable
    {
        yield from [
            ["String contains null char.\0"],
            ["String contains null char and line break.\0\n"],
        ];
    }
}
