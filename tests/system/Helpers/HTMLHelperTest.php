<?php
namespace CodeIgniter\Helpers;

final class HTMLHelperTest extends \CIUnitTestCase
{

	private $tracks;

	protected function setUp(): void
	{
		parent::setUp();

		//URL is needed by the HTML Helper.
		helper('url');
		helper('html');

		$this->tracks = [
			track('subtitles_no.vtt', 'subtitles', 'no', 'Norwegian No'),
			track('subtitles_yes.vtt', 'subtitles', 'yes', 'Norwegian Yes'),
		];
	}

	//--------------------------------------------------------------------

	public function testBasicUL()
	{
		$expected = <<<EOH
<ul>
  <li>foo</li>
  <li>bar</li>
</ul>

EOH;

		$expected = ltrim($expected);
		$list     = [
			'foo',
			'bar',
		];

		$this->assertEquals(ltrim($expected), ul($list));
	}

	public function testULWithClass()
	{
		$expected = <<<EOH
<ul class="test">
  <li>foo</li>
  <li>bar</li>
</ul>

EOH;

		$expected = ltrim($expected);
		$list     = [
			'foo',
			'bar',
		];

		$this->assertEquals($expected, ul($list, 'class="test"'));
	}

	public function testMultiLevelUL()
	{
		$expected = <<<EOH
<ul>
  <li>foo</li>
  <li>bar</li>
  <li>2
    <ul>
      <li>foo</li>
      <li>bar</li>
    </ul>
  </li>
</ul>

EOH;

		$expected = ltrim($expected);
		$list     = [
			'foo',
			'bar',
			[
				'foo',
				'bar',
			],
		];

		$this->assertEquals(ltrim($expected), ul($list));
	}

	//--------------------------------------------------------------------

	public function testBasicOL()
	{
		$expected = <<<EOH
<ol>
  <li>foo</li>
  <li>bar</li>
</ol>

EOH;

		$expected = ltrim($expected);
		$list     = [
			'foo',
			'bar',
		];

		$this->assertEquals(ltrim($expected), ol($list));
	}

	public function testOLWithClass()
	{
		$expected = <<<EOH
<ol class="test">
  <li>foo</li>
  <li>bar</li>
</ol>

EOH;

		$expected = ltrim($expected);
		$list     = [
			'foo',
			'bar',
		];

		$this->assertEquals($expected, ol($list, 'class="test"'));
	}

	public function testMultiLevelOL()
	{
		$expected = <<<EOH
<ol>
  <li>foo</li>
  <li>bar</li>
  <li>2
    <ol>
      <li>foo</li>
      <li>bar</li>
    </ol>
  </li>
</ol>

EOH;

		$expected = ltrim($expected);
		$list     = [
			'foo',
			'bar',
			[
				'foo',
				'bar',
			],
		];

		$this->assertEquals(ltrim($expected), ol($list));
	}

	// ------------------------------------------------------------------------

	public function testIMG()
	{
		$target   = 'http://site.com/images/picture.jpg';
		$expected = '<img src="http://site.com/images/picture.jpg" alt="" />';
		$this->assertEquals($expected, img($target));
	}

	public function testIMGWithoutProtocol()
	{
		$target   = 'assets/mugshot.jpg';
		$expected = '<img src="http://example.com/assets/mugshot.jpg" alt="" />';
		$this->assertEquals($expected, img($target));
	}

	public function testIMGWithIndexpage()
	{
		$target   = 'assets/mugshot.jpg';
		$expected = '<img src="http://example.com/index.php/assets/mugshot.jpg" alt="" />';
		$this->assertEquals($expected, img($target, true));
	}

	// ------------------------------------------------------------------------

	public function testScriptTag()
	{
		$target   = 'http://site.com/js/mystyles.js';
		$expected = '<script src="http://site.com/js/mystyles.js" type="text/javascript"></script>';
		$this->assertEquals($expected, script_tag($target));
	}

	public function testScriptTagWithoutProtocol()
	{
		$target   = 'js/mystyles.js';
		$expected = '<script src="http://example.com/js/mystyles.js" type="text/javascript"></script>';
		$this->assertEquals($expected, script_tag($target));
	}

	public function testScriptTagWithIndexpage()
	{
		$target   = 'js/mystyles.js';
		$expected = '<script src="http://example.com/index.php/js/mystyles.js" type="text/javascript"></script>';
		$this->assertEquals($expected, script_tag($target, true));
	}

	// ------------------------------------------------------------------------

	public function testLinkTag()
	{
		$target   = 'css/mystyles.css';
		$expected = '<link href="http://example.com/css/mystyles.css" rel="stylesheet" type="text/css" />';
		$this->assertEquals($expected, link_tag($target));
	}

	public function testLinkTagComplete()
	{
		$target   = 'https://styles.com/css/mystyles.css';
		$expected = '<link href="https://styles.com/css/mystyles.css" rel="banana" type="fruit" media="VHS" title="Go away" />';
		$this->assertEquals($expected, link_tag($target, 'banana', 'fruit', 'Go away', 'VHS'));
	}

	public function testLinkTagArray()
	{
		$parms    = [
			'href'      => 'css/mystyles.css',
			'indexPage' => true,
		];
		$expected = '<link href="http://example.com/index.php/css/mystyles.css" rel="stylesheet" type="text/css" />';
		$this->assertEquals($expected, link_tag($parms));
	}

	// ------------------------------------------------------------------------

	public function testDocType()
	{
		$target   = 'html4-strict';
		$expected = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';
		$this->assertEquals($expected, doctype($target));
	}

	public function testDocTypeDefault()
	{
		$expected = '<!DOCTYPE html>';
		$this->assertEquals($expected, doctype());
	}

	public function testDocTypeInvalid()
	{
		$target = 'good-guess';
		$this->assertEquals(false, doctype($target));
	}

	// ------------------------------------------------------------------------

	public function testVideo()
	{
		$expected = <<<EOH
<video src="http://www.codeigniter.com/test.mp4" controls>
  Your browser does not support the video tag.
</video>

EOH;

		$target  = 'http://www.codeigniter.com/test.mp4';
		$message = 'Your browser does not support the video tag.';
		$video   = video($target, $message, 'controls');
		$this->assertEquals($expected, $video);
	}

	public function testVideoWithTracks()
	{
		$expected = <<<EOH
<video src="http://example.com/test.mp4" controls>
  <track src="subtitles_no.vtt" kind="subtitles" srclang="no" label="Norwegian No" />
  <track src="subtitles_yes.vtt" kind="subtitles" srclang="yes" label="Norwegian Yes" />
  Your browser does not support the video tag.
</video>

EOH;

		$target  = 'test.mp4';
		$message = 'Your browser does not support the video tag.';
		$video   = video($target, $message, 'controls', $this->tracks);
		$this->assertEquals($expected, $video);
	}

	public function testVideoWithTracksAndIndex()
	{
		$expected = <<<EOH
<video src="http://example.com/index.php/test.mp4" controls>
  <track src="subtitles_no.vtt" kind="subtitles" srclang="no" label="Norwegian No" />
  <track src="subtitles_yes.vtt" kind="subtitles" srclang="yes" label="Norwegian Yes" />
  Your browser does not support the video tag.
</video>

EOH;

		$target  = 'test.mp4';
		$message = 'Your browser does not support the video tag.';
		$video   = video($target, $message, 'controls', $this->tracks, true);
		$this->assertEquals($expected, $video);
	}

	public function testVideoMultipleSources()
	{
		$expected = <<<EOH
<video class="test" controls>
  <source src="http://example.com/movie.mp4" type="video/mp4" class="test" />
  <source src="http://example.com/movie.ogg" type="video/ogg" />
  <source src="http://example.com/movie.mov" type="video/quicktime" />
  <source src="http://example.com/movie.ogv" type="video/ogv; codecs=dirac, speex" />
  <track src="subtitles_no.vtt" kind="subtitles" srclang="no" label="Norwegian No" />
  <track src="subtitles_yes.vtt" kind="subtitles" srclang="yes" label="Norwegian Yes" />
  Your browser does not support the video tag.
</video>

EOH;

		$sources = [
			source('movie.mp4', 'video/mp4', 'class="test"'),
			source('movie.ogg', 'video/ogg'),
			source('movie.mov', 'video/quicktime'),
			source('movie.ogv', 'video/ogv; codecs=dirac, speex'),
		];
		$message = 'Your browser does not support the video tag.';
		$video   = video($sources, $message, 'class="test" controls', $this->tracks);

		$this->assertEquals($expected, $video);
	}

	// ------------------------------------------------------------------------

	public function testAudio()
	{
		$expected = <<<EOH
<audio id="test" controls>
  <source src="http://example.com/sound.ogg" type="audio/ogg" />
  <source src="http://example.com/sound.mpeg" type="audio/mpeg" />
  <track src="subtitles_no.vtt" kind="subtitles" srclang="no" label="Norwegian No" />
  <track src="subtitles_yes.vtt" kind="subtitles" srclang="yes" label="Norwegian Yes" />
  Your browser does not support the audio tag.
</audio>

EOH;

		$sources = [
			source('sound.ogg', 'audio/ogg'),
			source('sound.mpeg', 'audio/mpeg'),
		];
		$message = 'Your browser does not support the audio tag.';
		$audio   = audio($sources, $message, 'id="test" controls', $this->tracks);

		$this->assertEquals($expected, $audio);
	}

	public function testAudioSimple()
	{
		$expected = <<<EOH
<audio src="http://example.com/sound.mpeg" type="audio/mpeg" id="test" controls>
  Your browser does not support the audio tag.
</audio>

EOH;

		$source  = 'sound.mpeg';
		$message = 'Your browser does not support the audio tag.';
		$audio   = audio($source, $message, 'type="audio/mpeg" id="test" controls');

		$this->assertEquals($expected, $audio);
	}

	public function testAudioWithSource()
	{
		$expected = <<<EOH
<audio src="http://codeigniter.com/sound.mpeg" type="audio/mpeg" id="test" controls>
  Your browser does not support the audio tag.
</audio>

EOH;

		$source  = 'http://codeigniter.com/sound.mpeg';
		$message = 'Your browser does not support the audio tag.';
		$audio   = audio($source, $message, 'type="audio/mpeg" id="test" controls');

		$this->assertEquals($expected, $audio);
	}

	public function testAudioWithIndex()
	{
		$expected = <<<EOH
<audio src="http://example.com/index.php/sound.mpeg" type="audio/mpeg" id="test" controls>
  Your browser does not support the audio tag.
</audio>

EOH;

		$source  = 'sound.mpeg';
		$message = 'Your browser does not support the audio tag.';
		$audio   = audio($source, $message, 'type="audio/mpeg" id="test" controls', [], true);

		$this->assertEquals($expected, $audio);
	}

	public function testAudioWithTracks()
	{
		$expected = <<<EOH
<audio src="http://example.com/sound.mpeg" type="audio/mpeg" id="test" controls>
  <track src="subtitles_no.vtt" kind="subtitles" srclang="no" label="Norwegian No" />
  <track src="subtitles_yes.vtt" kind="subtitles" srclang="yes" label="Norwegian Yes" />
  Your browser does not support the audio tag.
</audio>

EOH;

		$source  = 'sound.mpeg';
		$message = 'Your browser does not support the audio tag.';
		$audio   = audio($source, $message, 'type="audio/mpeg" id="test" controls', $this->tracks);

		$this->assertEquals($expected, $audio);
	}

	// ------------------------------------------------------------------------

	public function testMediaNameOnly()
	{
		$expected = <<<EOH
<av>
</av>

EOH;
		$this->assertEquals($expected, _media('av'));
	}

	public function testMediaWithSources()
	{
		$expected = <<<EOH
<av>
  <source src="http://example.com/sound.ogg" type="audio/ogg" />
  <source src="http://example.com/sound.mpeg" type="audio/mpeg" />
</av>

EOH;
		$sources  = [
			source('sound.ogg', 'audio/ogg'),
			source('sound.mpeg', 'audio/mpeg'),
		];
		$this->assertEquals($expected, _media('av', $sources));
	}

	public function testSource()
	{
		$expected = '<source src="http://example.com/index.php/sound.mpeg" type="audio/mpeg" />';
		$this->assertEquals($expected, source('sound.mpeg', 'audio/mpeg', '', true));
	}

	// ------------------------------------------------------------------------

	public function testEmbed()
	{
		$expected = <<<EOH
<embed src="http://example.com/movie.mov" type="video/quicktime" class="test" />

EOH;

		$type  = 'video/quicktime';
		$embed = embed('movie.mov', $type, 'class="test"');
		$this->assertEquals($expected, $embed);
	}

	public function testEmbedIndexed()
	{
		$expected = <<<EOH
<embed src="http://example.com/index.php/movie.mov" type="video/quicktime" class="test" />

EOH;

		$type  = 'video/quicktime';
		$embed = embed('movie.mov', $type, 'class="test"', true);
		$this->assertEquals($expected, $embed, '');
	}

	public function testObject()
	{
		$expected = <<<EOH
<object data="http://example.com/movie.swf" class="test"></object>

EOH;

		$type   = 'application/x-shockwave-flash';
		$object = object('movie.swf', $type, 'class="test"');

		$this->assertEquals($expected, $object);
	}

	public function testObjectWithParams()
	{
		$expected = <<<EOH
<object data="http://example.com/movie.swf" class="test">
  <param name="foo" type="ref" value="bar" class="test" />
  <param name="hello" type="ref" value="world" class="test" />
</object>

EOH;

		$type   = 'application/x-shockwave-flash';
		$parms  = [
			param('foo', 'bar', 'ref', 'class="test"'),
			param('hello', 'world', 'ref', 'class="test"'),
		];
		$object = object('movie.swf', $type, 'class="test"', $parms);
		$this->assertEquals($expected, $object);
	}

	public function testObjectIndexed()
	{
		$expected = <<<EOH
<object data="http://example.com/index.php/movie.swf" class="test"></object>

EOH;

		$type   = 'application/x-shockwave-flash';
		$object = object('movie.swf', $type, 'class="test"', [], true);

		$this->assertEquals($expected, $object);
	}

	// ------------------------------------------------------------------------
}
