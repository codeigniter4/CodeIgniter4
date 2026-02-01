<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Filters;

use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\SiteURI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Security\Exceptions\SecurityException;
use CodeIgniter\Superglobals;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockAppConfig;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class InvalidCharsTest extends CIUnitTestCase
{
    private InvalidChars $invalidChars;
    private IncomingRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        Services::injectMock('superglobals', new Superglobals(null, [], [], []));

        $this->request      = $this->createRequest();
        $this->invalidChars = new InvalidChars();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $superglobals = service('superglobals');
        $superglobals->setGetArray([]);
        $superglobals->setPostArray([]);
        $superglobals->setCookieArray([]);
    }

    private function createRequest(): IncomingRequest
    {
        $config    = new MockAppConfig();
        $uri       = new SiteURI($config);
        $userAgent = new UserAgent();
        $request   = $this->getMockBuilder(IncomingRequest::class)
            ->setConstructorArgs([$config, $uri, null, $userAgent])
            ->onlyMethods(['isCLI'])
            ->getMock();
        $request->method('isCLI')->willReturn(false);

        return $request;
    }

    #[DoesNotPerformAssertions]
    public function testBeforeDoNothingWhenCLIRequest(): void
    {
        $cliRequest = new CLIRequest(new MockAppConfig());

        $this->invalidChars->before($cliRequest);
    }

    #[DoesNotPerformAssertions]
    public function testBeforeValidString(): void
    {
        $superglobals = service('superglobals');
        $superglobals->setPost('val', [
            'valid string',
        ]);
        $superglobals->setCookie('val', 'valid string');

        $this->invalidChars->before($this->request);
    }

    public function testBeforeInvalidUTF8StringCausesException(): void
    {
        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('Invalid UTF-8 characters in post:');

        $sjisString = mb_convert_encoding('SJISの文字列です。', 'SJIS');
        service('superglobals')->setPost('val', [
            'valid string',
            $sjisString,
        ]);

        $this->invalidChars->before($this->request);
    }

    public function testBeforeInvalidControlCharCausesException(): void
    {
        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('Invalid Control characters in cookie:');

        $stringWithNullChar = "String contains null char and line break.\0\n";
        service('superglobals')->setCookie('val', $stringWithNullChar);

        $this->invalidChars->before($this->request);
    }

    #[DataProvider('provideCheckControlStringWithLineBreakAndTabReturnsTheString')]
    #[DoesNotPerformAssertions]
    public function testCheckControlStringWithLineBreakAndTabReturnsTheString(string $input): void
    {
        service('superglobals')->setGet('val', $input);

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

    #[DataProvider('provideCheckControlStringWithControlCharsCausesException')]
    public function testCheckControlStringWithControlCharsCausesException(string $input): void
    {
        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('Invalid Control characters in get:');

        service('superglobals')->setGet('val', $input);

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
