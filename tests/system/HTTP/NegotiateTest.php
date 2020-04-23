<?php

namespace CodeIgniter\HTTP;

use Config\App;

class NegotiateTest extends \CodeIgniter\Test\CIUnitTestCase
{

	/**
	 * @var CodeIgniter\HTTP\Request
	 */
	protected $request;

	/**
	 * @var \CodeIgniter\HTTP\Negotiate
	 */
	protected $negotiate;

	protected function setUp(): void
	{
		parent::setUp();

		$this->request = new Request(new App());

		$this->negotiate = new Negotiate($this->request);
	}

	//--------------------------------------------------------------------

	public function tearDown(): void
	{
		$this->request = $this->negotiate = null;
		unset($this->request, $this->negotiate);
	}

	//--------------------------------------------------------------------

	public function testNegotiateMediaFindsHighestMatch()
	{
		$this->request->setHeader('Accept', 'text/plain; q=0.5, text/html, text/x-dvi; q=0.8, text/x-c');
		$this->negotiate->setRequest($this->request);

		$this->assertEquals('text/html', $this->negotiate->media(['text/html', 'text/x-c', 'text/x-dvi', 'text/plain']));
		$this->assertEquals('text/x-c', $this->negotiate->media(['text/x-c', 'text/x-dvi', 'text/plain']));
		$this->assertEquals('text/x-dvi', $this->negotiate->media(['text/plain', 'text/x-dvi']));
		$this->assertEquals('text/x-dvi', $this->negotiate->media(['text/x-dvi']));

		// No matches? Return the first that we support...
		$this->assertEquals('text/md', $this->negotiate->media(['text/md']));
	}

	//--------------------------------------------------------------------

	public function testParseHeaderDeterminesCorrectPrecedence()
	{
		$header = $this->negotiate->parseHeader('text/*, text/plain, text/plain;format=flowed, */*');

		$this->assertEquals('text/plain', $header[0]['value']);
		$this->assertEquals('flowed', $header[0]['params']['format']);
		$this->assertEquals('text/plain', $header[1]['value']);
		$this->assertEquals('*/*', $header[3]['value']);
	}

	//--------------------------------------------------------------------

	public function testNegotiateMediaReturnsSupportedMatchWhenAsterisksInAvailable()
	{
		$this->request->setHeader('Accept', 'image/*, text/*');

		$this->assertEquals('text/plain', $this->negotiate->media(['text/plain']));
	}

	//--------------------------------------------------------------------

	public function testNegotiateMediaRecognizesMediaTypes()
	{
		// Image has a higher specificity, but is the wrong type...
		$this->request->setHeader('Accept', 'text/*, image/jpeg');

		$this->assertEquals('text/plain', $this->negotiate->media(['text/plain']));
	}

	//--------------------------------------------------------------------

	public function testNegotiateMediaSupportsStrictMatching()
	{
		// Image has a higher specificity, but is the wrong type...
		$this->request->setHeader('Accept', 'text/md, image/jpeg');

		$this->assertEquals('text/plain', $this->negotiate->media(['text/plain']));
		$this->assertEquals('', $this->negotiate->media(['text/plain'], true));
	}

	//--------------------------------------------------------------------

	/**
	 * @group single
	 */
	public function testAcceptCharsetMatchesBasics()
	{
		$this->request->setHeader('Accept-Charset', 'iso-8859-5, unicode-1-1;q=0.8');

		$this->assertEquals('iso-8859-5', $this->negotiate->charset(['iso-8859-5', 'unicode-1-1']));
		$this->assertEquals('unicode-1-1', $this->negotiate->charset(['utf-8', 'unicode-1-1']));

		// No match will default to utf-8
		$this->assertEquals('utf-8', $this->negotiate->charset(['iso-8859', 'unicode-1-2']));
	}

	//--------------------------------------------------------------------

	public function testNegotiateEncodingReturnsFirstIfNoAcceptHeaderExists()
	{
		$this->assertEquals('compress', $this->negotiate->encoding(['compress', 'gzip']));
	}

	//--------------------------------------------------------------------

	public function testNegotiatesEncodingBasics()
	{
		$this->request->setHeader('Accept-Encoding', 'gzip;q=1.0, identity; q=0.4, compress;q=0.5');

		$this->assertEquals('gzip', $this->negotiate->encoding(['gzip', 'compress']));
		$this->assertEquals('compress', $this->negotiate->encoding(['compress']));
		$this->assertEquals('identity', $this->negotiate->encoding());
	}

	//--------------------------------------------------------------------

	public function testAcceptLanguageBasics()
	{
		$this->request->setHeader('Accept-Language', 'da, en-gb;q=0.8, en;q=0.7');

		$this->assertEquals('da', $this->negotiate->language(['da', 'en']));
		$this->assertEquals('en-gb', $this->negotiate->language(['en-gb', 'en']));
		$this->assertEquals('en', $this->negotiate->language(['en']));
	}

	//--------------------------------------------------------------------

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/2774
	 */
	public function testAcceptLanguageMatchesBroadly()
	{
		$this->request->setHeader('Accept-Language', 'fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7');

		$this->assertEquals('fr', $this->negotiate->language(['fr', 'en']));
	}

	public function testBestMatchEmpty()
	{
		$this->expectException(Exceptions\HTTPException::class);
		$this->negotiate->media([]);
	}

	public function testBestMatchNoHeader()
	{
		$this->request->setHeader('Accept', '');
		$this->assertEquals('', $this->negotiate->media(['apple', 'banana'], true));
		$this->assertEquals('apple/mac', $this->negotiate->media(['apple/mac', 'banana/yellow'], false));
	}

	public function testBestMatchNotAcceptable()
	{
		$this->request->setHeader('Accept', 'popcorn/cheddar');
		$this->assertEquals('apple/mac', $this->negotiate->media(['apple/mac', 'banana/yellow'], false));
		$this->assertEquals('banana/yellow', $this->negotiate->media(['banana/yellow', 'apple/mac'], false));
	}

	public function testBestMatchFirstSupported()
	{
		$this->request->setHeader('Accept', 'popcorn/cheddar, */*');
		$this->assertEquals('apple/mac', $this->negotiate->media(['apple/mac', 'banana/yellow'], false));
	}

	public function testBestMatchLowQuality()
	{
		$this->request->setHeader('Accept', 'popcorn/cheddar;q=0, apple/mac, */*');
		$this->assertEquals('apple/mac', $this->negotiate->media(['apple/mac', 'popcorn/cheddar'], false));
		$this->assertEquals('apple/mac', $this->negotiate->media(['popcorn/cheddar', 'apple/mac'], false));
	}

	public function testBestMatchOnlyLowQuality()
	{
		$this->request->setHeader('Accept', 'popcorn/cheddar;q=0');
		// the first supported should be returned, since nothing will make us happy
		$this->assertEquals('apple/mac', $this->negotiate->media(['apple/mac', 'popcorn/cheddar'], false));
		$this->assertEquals('popcorn/cheddar', $this->negotiate->media(['popcorn/cheddar', 'apple/mac'], false));
	}

	public function testParameterMatching()
	{
		$this->request->setHeader('Accept', 'popcorn/cheddar;a=0;b=1');
		$this->assertEquals('popcorn/cheddar;a=2', $this->negotiate->media(['popcorn/cheddar;a=2'], false));
		$this->assertEquals('popcorn/cheddar;a=0', $this->negotiate->media(['popcorn/cheddar;a=0', 'popcorn/cheddar;a=2;b=1'], false));
	}

}
