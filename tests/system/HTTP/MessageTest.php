<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\HTTP;

use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 *
 * @group Others
 */
final class MessageTest extends CIUnitTestCase
{
    private ?Message $message;

    protected function setUp(): void
    {
        parent::setUp();

        $this->message = new Message();
    }

    // We can only test the headers retrieved from $_SERVER
    // This test might fail under apache.
    public function testHeadersRetrievesHeaders(): void
    {
        $this->message->setHeader('Host', 'daisyduke.com');
        $this->message->setHeader('Referer', 'RoscoePekoTrain.com');

        $headers = $this->message->headers();

        // Content-Type is likely set...
        $this->assertGreaterThanOrEqual(2, count($headers));

        $this->assertSame('daisyduke.com', $headers['Host']->getValue());
        $this->assertSame('RoscoePekoTrain.com', $headers['Referer']->getValue());
    }

    public function testCanGrabSingleHeader(): void
    {
        $this->message->setHeader('Host', 'daisyduke.com');

        $header = $this->message->header('Host');

        $this->assertInstanceOf(Header::class, $header);
        $this->assertSame('daisyduke.com', $header->getValue());
    }

    public function testCaseInsensitiveheader(): void
    {
        $this->message->setHeader('Host', 'daisyduke.com');

        $this->assertSame('daisyduke.com', $this->message->header('host')->getValue());
        $this->assertSame('daisyduke.com', $this->message->header('HOST')->getValue());
    }

    public function testCanSetHeaders(): void
    {
        $this->message->setHeader('first', 'kiss');
        $this->message->setHeader('second', ['black', 'book']);

        $this->assertSame('kiss', $this->message->header('FIRST')->getValue());
        $this->assertSame(['black', 'book'], $this->message->header('Second')->getValue());
    }

    public function testSetHeaderOverwritesPrevious(): void
    {
        $this->message->setHeader('Pragma', 'cache');
        $this->message->setHeader('Pragma', 'no-cache');

        $this->assertSame('no-cache', $this->message->header('Pragma')->getValue());
    }

    public function testHeaderLineIsReadable(): void
    {
        $this->message->setHeader('Accept', ['json', 'html']);
        $this->message->setHeader('Host', 'daisyduke.com');

        $this->assertSame('json, html', $this->message->header('Accept')->getValueLine());
        $this->assertSame('daisyduke.com', $this->message->header('Host')->getValueLine());
    }

    public function testCanRemoveHeader(): void
    {
        $this->message->setHeader('Host', 'daisyduke.com');

        $this->message->removeHeader('host');

        $this->assertNull($this->message->header('host'));
    }

    public function testCanAppendHeader(): void
    {
        $this->message->setHeader('accept', ['json', 'html']);

        $this->message->appendHeader('Accept', 'xml');

        $this->assertSame(['json', 'html', 'xml'], $this->message->header('accept')->getValue());
    }

    public function testCanPrependHeader(): void
    {
        $this->message->setHeader('accept', ['json', 'html']);

        $this->message->prependHeader('Accept', 'xml');

        $this->assertSame(['xml', 'json', 'html'], $this->message->header('accept')->getValue());
    }

    public function testSetProtocolWorks(): void
    {
        $this->message->setProtocolVersion('1.1');

        $this->assertSame('1.1', $this->message->getProtocolVersion());
    }

    public function testSetProtocolWorksWithNonNumericVersion(): void
    {
        $this->message->setProtocolVersion('HTTP/1.1');

        $this->assertSame('1.1', $this->message->getProtocolVersion());
    }

    public function testSetProtocolThrowsExceptionWithInvalidProtocol(): void
    {
        $this->expectException(HTTPException::class);
        $this->message->setProtocolVersion('1.2');
    }

    public function testBodyBasics(): void
    {
        $body = 'a strange little fellow.';

        $this->message->setBody($body);

        $this->assertSame($body, $this->message->getBody());
    }

    public function testAppendBody(): void
    {
        $this->message->setBody('moo');

        $this->message->appendBody("\n");

        $this->assertSame("moo\n", $this->message->getBody());
    }

    public function testSetHeaderReplacingHeader(): void
    {
        $this->message->setHeader('Accept', 'json');

        $this->assertSame('json', $this->message->getHeaderLine('Accept'));
    }

    public function testSetHeaderDuplicateSettings(): void
    {
        $this->message->setHeader('Accept', 'json');
        $this->message->setHeader('Accept', 'xml');

        $this->assertSame('xml', $this->message->getHeaderLine('Accept'));
    }

    public function testSetHeaderDuplicateSettingsInsensitive(): void
    {
        $this->message->setHeader('Accept', 'json');
        $this->message->setHeader('accept', 'xml');

        $this->assertSame('xml', $this->message->getHeaderLine('Accept'));
    }

    public function testSetHeaderArrayValues(): void
    {
        $this->message->setHeader('Accept', ['json', 'html', 'xml']);

        $this->assertSame('json, html, xml', $this->message->getHeaderLine('Accept'));
    }

    public static function provideArrayHeaderValue(): iterable
    {
        return [
            'existing for next not append' => [
                [
                    'json',
                    'html',
                    'xml',
                ],
            ],
            'existing for next append' => [
                [
                    'json',
                    'html',
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideArrayHeaderValue
     *
     * @param mixed $arrayHeaderValue
     */
    public function testSetHeaderWithExistingArrayValuesAppendStringValue($arrayHeaderValue): void
    {
        $this->message->setHeader('Accept', $arrayHeaderValue);
        $this->message->setHeader('Accept', 'xml');

        $this->assertSame('json, html, xml', $this->message->getHeaderLine('Accept'));
    }

    /**
     * @dataProvider provideArrayHeaderValue
     *
     * @param mixed $arrayHeaderValue
     */
    public function testSetHeaderWithExistingArrayValuesAppendArrayValue($arrayHeaderValue): void
    {
        $this->message->setHeader('Accept', $arrayHeaderValue);
        $this->message->setHeader('Accept', ['xml']);

        $this->assertSame('json, html, xml', $this->message->getHeaderLine('Accept'));
    }

    public function testSetHeaderWithExistingArrayValuesAppendNullValue(): void
    {
        $this->message->setHeader('Accept', ['json', 'html', 'xml']);
        $this->message->setHeader('Accept', null);

        $this->assertSame('json, html, xml', $this->message->getHeaderLine('Accept'));
    }

    public function testPopulateHeadersWithoutContentType(): void
    {
        $original    = $_SERVER;
        $originalEnv = getenv('CONTENT_TYPE');

        // fail path, if the CONTENT_TYPE doesn't exist
        $_SERVER = ['HTTP_ACCEPT_LANGUAGE' => 'en-us,en;q=0.50'];
        putenv('CONTENT_TYPE');

        $this->message->populateHeaders();

        $this->assertNull($this->message->header('content-type'));

        putenv("CONTENT_TYPE={$originalEnv}");
        $_SERVER = $original; // restore so code coverage doesn't break
    }

    public function testPopulateHeadersWithoutHTTP(): void
    {
        // fail path, if argument doesn't have the HTTP_*
        $original = $_SERVER;
        $_SERVER  = [
            'USER_AGENT'     => 'Mozilla/5.0 (iPad; U; CPU OS 3_2_1 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Mobile/7B405',
            'REQUEST_METHOD' => 'POST',
        ];

        $this->message->populateHeaders();

        $this->assertNull($this->message->header('user-agent'));
        $this->assertNull($this->message->header('request-method'));

        $_SERVER = $original; // restore so code coverage doesn't break
    }

    public function testPopulateHeadersKeyNotExists(): void
    {
        // Success path, if array key is not exists, assign empty string to it's value
        $original = $_SERVER;
        $_SERVER  = [
            'CONTENT_TYPE'        => 'text/html; charset=utf-8',
            'HTTP_ACCEPT_CHARSET' => null,
        ];

        $this->message->populateHeaders();

        $this->assertSame('', $this->message->header('accept-charset')->getValue());

        $_SERVER = $original; // restore so code coverage doesn't break
    }

    public function testPopulateHeaders(): void
    {
        // success path
        $original = $_SERVER;
        $_SERVER  = [
            'CONTENT_TYPE'         => 'text/html; charset=utf-8',
            'HTTP_ACCEPT_LANGUAGE' => 'en-us,en;q=0.50',
        ];

        $this->message->populateHeaders();

        $this->assertSame('text/html; charset=utf-8', $this->message->header('content-type')->getValue());
        $this->assertSame('en-us,en;q=0.50', $this->message->header('accept-language')->getValue());

        $_SERVER = $original; // restore so code coverage doesn't break
    }
}
