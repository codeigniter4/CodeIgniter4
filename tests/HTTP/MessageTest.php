<?php

class MessageTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var CodeIgniter\HTTP\Message
	 */
	protected $message;

	public function setUp()
	{
	    $this->message = new \CodeIgniter\HTTP\Message();
	}

	//--------------------------------------------------------------------

	public function tearDown()
	{
		$this->message = null;
	    unset($this->message);
	}

	//--------------------------------------------------------------------


	// We can only test the headers retrieved from $_SERVER
	// This test might fail under apache.
	public function testHeadersRetrievesHeaders()
	{
		$this->message->setHeader('Host', 'daisyduke.com');
		$this->message->setHeader('Referer', 'RoscoePekoTrain.com');

		$headers = $this->message->headers();

		// Content-Type is likely set...
		$this->assertTrue(count($headers) >= 2);

		$this->assertTrue($headers['Host'] == 'daisyduke.com');
		$this->assertTrue($headers['Referer'] == 'RoscoePekoTrain.com');
	}

	//--------------------------------------------------------------------

	public function testCanGrabSingleHeader()
	{
		$this->message->setHeader('Host', 'daisyduke.com');

	    $this->assertEquals('daisyduke.com', $this->message->header('Host'));
	}

	//--------------------------------------------------------------------

	public function testCaseInsensitveGetHeader()
	{
		$this->message->setHeader('Host', 'daisyduke.com');

		$this->assertEquals('daisyduke.com', $this->message->header('host'));
		$this->assertEquals('daisyduke.com', $this->message->header('HOST'));
	}

	//--------------------------------------------------------------------

	public function testCanSetHeaders()
	{
	    $this->message->setHeader('first', 'kiss');
		$this->message->setHeader('second', ['black', 'book']);

		$this->assertEquals('kiss', $this->message->header('FIRST'));
		$this->assertEquals(['black', 'book'], $this->message->header('Second'));
	}

	//--------------------------------------------------------------------

	public function testHeaderLineIsReadable()
	{
		$this->message->setHeader('Accept', ['json', 'html']);
		$this->message->setHeader('Host', 'daisyduke.com');

		$this->assertEquals('json, html', $this->message->headerLine('Accept'));
		$this->assertEquals('daisyduke.com', $this->message->headerLine('Host'));
	}

	//--------------------------------------------------------------------

	public function testCanRemoveHeader()
	{
		$this->message->setHeader('Host', 'daisyduke.com');

		$this->message->removeHeader('host');

		$this->assertEquals('', $this->message->header('host'));
	}

	//--------------------------------------------------------------------

	public function testCanAppendHeader()
	{
	    $this->message->setHeader('accept', ['json', 'html']);

		$this->message->appendHeader('Accept', 'xml');

		$this->assertEquals(['json', 'html', 'xml'], $this->message->header('accept'));
	}

	//--------------------------------------------------------------------

	public function testAppendHeaderThrowsExceptionOnNotArray()
	{
	    $this->message->setHeader('accept', 'json');

		$this->setExpectedException('LogicException');

		$this->message->appendHeader('Accept', 'xml');
	}

	//--------------------------------------------------------------------

	public function testSetProtocolWorks()
	{
	    $this->message->setProtocolVersion('1.1');

		$this->assertEquals('1.1', $this->message->protocolVersion());
	}

	//--------------------------------------------------------------------

	public function testSetProtocolThrowsExceptionWithInvalidProtocol()
	{
		$this->setExpectedException('InvalidArgumentException');
	    $this->message->setProtocolVersion('1.2');
	}

	//--------------------------------------------------------------------

	public function testBodyBasics()
	{
		$body = 'a strange little fellow.';

	    $this->message->setBody($body);

		$this->assertEquals($body, $this->message->body());
	}

	//--------------------------------------------------------------------

	public function testNegotiateMediaFindsHighestMatch()
	{
	    $this->message->setHeader('Accept', 'text/plain; q=0.5, text/html, text/x-dvi; q=0.8, text/x-c');

		$this->assertEquals('text/html', $this->message->negotiateMedia(['text/html', 'text/x-c', 'text/x-dvi', 'text/plain']));
		$this->assertEquals('text/x-c', $this->message->negotiateMedia(['text/x-c', 'text/x-dvi', 'text/plain']));
		$this->assertEquals('text/x-dvi', $this->message->negotiateMedia(['text/plain', 'text/x-dvi']));
		$this->assertEquals('text/x-dvi', $this->message->negotiateMedia(['text/x-dvi']));

		// No matches? Return the first that we support...
		$this->assertEquals('text/md', $this->message->negotiateMedia(['text/md']));
	}
	
	//--------------------------------------------------------------------

	public function testParseHeaderDeterminesCorrectPrecedence()
	{
	    $header =$this->message->parseHeader('text/*, text/plain, text/plain;format=flowed, */*');

		$this->assertEquals('text/plain', $header[0]['value']);
		$this->assertEquals('flowed', $header[0]['params']['format']);
		$this->assertEquals('text/plain', $header[1]['value']);
		$this->assertEquals('*/*', $header[3]['value']);
	}

	//--------------------------------------------------------------------

	public function testNegotiateMediaReturnsSupportedMatchWhenAsterisksInAvailable()
	{
	    $this->message->setHeader('Accept', 'image/*, text/*');

		$this->assertEquals('text/plain', $this->message->negotiateMedia(['text/plain']));
	}

	//--------------------------------------------------------------------

	public function testNegotiateMediaRecognizesMediaTypes()
	{
		// Image has a higher specificity, but is the wrong type...
	    $this->message->setHeader('Accept', 'text/*, image/jpeg');

		$this->assertEquals('text/plain', $this->message->negotiateMedia(['text/plain']));
	}

	//--------------------------------------------------------------------

	public function testNegotiateMediaSupportsStrictMatching()
	{
		// Image has a higher specificity, but is the wrong type...
		$this->message->setHeader('Accept', 'text/md, image/jpeg');

		$this->assertEquals('text/plain', $this->message->negotiateMedia(['text/plain']));
		$this->assertEquals('', $this->message->negotiateMedia(['text/plain'], true));
	}

	//--------------------------------------------------------------------

	public function testAcceptCharsetMatchesBasics()
	{
	    $this->message->setHeader('Accept-Charset', 'iso-8859-5, unicode-1-1;q=0.8');

		$this->assertEquals('iso-8859-5', $this->message->negotiateCharset(['iso-8859-5', 'unicode-1-1']));
		$this->assertEquals('unicode-1-1', $this->message->negotiateCharset(['utf-8', 'unicode-1-1']));

		// No match will default to utf-8
		$this->assertEquals('utf-8', $this->message->negotiateCharset(['iso-8859', 'unicode-1-2']));
	}

	//--------------------------------------------------------------------

	public function testNegotiateEncodingReturnsFirstIfNoAcceptHeaderExists()
	{
		$this->assertEquals('compress', $this->message->negotiateEncoding(['compress', 'gzip']));
	}
	
	//--------------------------------------------------------------------
	
	public function testNegotiatesEncodingBasics()
	{
	    $this->message->setHeader('Accept-Encoding', 'gzip;q=1.0, identity; q=0.4, compress;q=0.5');

		$this->assertEquals('gzip', $this->message->negotiateEncoding(['gzip', 'compress']));
		$this->assertEquals('compress', $this->message->negotiateEncoding(['compress']));
		$this->assertEquals('identity', $this->message->negotiateEncoding());
	}
	
	//--------------------------------------------------------------------
	
	public function testAcceptLanguageBasics()
	{
	    $this->message->setHeader('Accept-Language', 'da, en-gb;q=0.8, en;q=0.7');

		$this->assertEquals('da', $this->message->negotiateLanguage(['da', 'en']));
		$this->assertEquals('en-gb', $this->message->negotiateLanguage(['en-gb', 'en']));
		$this->assertEquals('en', $this->message->negotiateLanguage(['en']));
	}
	
	//--------------------------------------------------------------------
	
	
}
