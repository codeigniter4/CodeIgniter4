<?php namespace CodeIgniter\HTTP;

use CodeIgniter\HTTP\URI;
use Config\App;
use CodeIgniter\Services;

final class HTMLHelperTest extends \CIUnitTestCase
{

	private $tracks;

	public function setUp()
	{
		//URL is needed by the HTML Helper.
		helper('url');
		helper('html');

		$this->tracks = 
		[
			track('subtitles_no.vtt',  'subtitles', 'no',  'Norwegian No'),
			track('subtitles_yes.vtt', 'subtitles', 'yes', 'Norwegian Yes')
		];
	}

	//--------------------------------------------------------------------
	
	public function testUL()
	{
		$expected = <<<EOH
<ul>
  <li>foo</li>
  <li>bar</li>
</ul>

EOH;

		$expected = ltrim($expected);
		$list     = array('foo', 'bar');

		$this->assertEquals(ltrim($expected), ul($list));

		$expected = <<<EOH
<ul class="test">
  <li>foo</li>
  <li>bar</li>
</ul>

EOH;

		$expected = ltrim($expected);
		$this->assertEquals($expected, ul($list, 'class="test"'));
	}

	// ------------------------------------------------------------------------

	public function testIMG()
	{
		//TODO: Mock baseURL and siteURL.
		$this->assertEquals
		(
			'<img src="http://site.com/images/picture.jpg" alt="" />', 
			img('http://site.com/images/picture.jpg')
		);
	}

	// ------------------------------------------------------------------------

	public function testScriptTag()
	{
		$this->assertEquals
		(
			'<script src="http://site.com/js/mystyles.js" type="text/javascript"></script>',
			script_tag('http://site.com/js/mystyles.js')
		);
	}

	// ------------------------------------------------------------------------

	public function testLinkTag()
	{
		$this->assertEquals
		(
			'<link href="http://site.com/css/mystyles.css" rel="stylesheet" type="text/css" />',
			link_tag('http://site.com/css/mystyles.css')
		);
	}

	// ------------------------------------------------------------------------

	public function testDocType()
	{
		$this->assertEquals
		(
			'<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">',
			doctype('html4-strict')
		);
	}

	// ------------------------------------------------------------------------

	public function testVideo()
	{
		$expected = <<<EOH
<video src="test.mp4" controls>
  Your browser does not support the video tag.
</video>

EOH;

		$video = video
		(
			'test.mp4', 
			'Your browser does not support the video tag.', 
			'controls'
		);

		$this->assertEquals($expected, $video);

		$expected = <<<EOH
<video src="http://www.codeigniter.com/test.mp4" controls>
  <track src="subtitles_no.vtt" kind="subtitles" srclang="no" label="Norwegian No" />
  <track src="subtitles_yes.vtt" kind="subtitles" srclang="yes" label="Norwegian Yes" />
  Your browser does not support the video tag.
</video>

EOH;

		$video = video
		(
			'http://www.codeigniter.com/test.mp4', 
			'Your browser does not support the video tag.', 
			'controls',
			$this->tracks
		);
		$this->assertEquals($expected, $video);

		$expected = <<<EOH
<video class="test" controls>
  <source src="movie.mp4" type="video/mp4" class="test" />
  <source src="movie.ogg" type="video/ogg" />
  <source src="movie.mov" type="video/quicktime" />
  <source src="movie.ogv" type="video/ogv; codecs=dirac, speex" />
  <track src="subtitles_no.vtt" kind="subtitles" srclang="no" label="Norwegian No" />
  <track src="subtitles_yes.vtt" kind="subtitles" srclang="yes" label="Norwegian Yes" />
  Your browser does not support the video tag.
</video>

EOH;

		$video = video
		(
			[
			  source('movie.mp4', 'video/mp4', 'class="test"'),
			  source('movie.ogg', 'video/ogg'),
			  source('movie.mov', 'video/quicktime'),
			  source('movie.ogv', 'video/ogv; codecs=dirac, speex')
			],
			'Your browser does not support the video tag.',
			'class="test" controls',
			$this->tracks
		 );

		 $this->assertEquals($expected, $video);
	}

	// ------------------------------------------------------------------------

	public function testAudio()
	{
		$expected = <<<EOH
<audio id="test" controls>
  <source src="sound.ogg" type="audio/ogg" />
  <source src="sound.mpeg" type="audio/mpeg" />
  <track src="subtitles_no.vtt" kind="subtitles" srclang="no" label="Norwegian No" />
  <track src="subtitles_yes.vtt" kind="subtitles" srclang="yes" label="Norwegian Yes" />
  Your browser does not support the audio tag.
</audio>

EOH;

		$audio = audio
		(
			[
				source('sound.ogg', 'audio/ogg'),
				source('sound.mpeg', 'audio/mpeg')
			],
			'Your browser does not support the audio tag.',
			'id="test" controls',
			$this->tracks
		 );

		$this->assertEquals($expected, $audio);
	}

	// ------------------------------------------------------------------------

	public function testEmbed()
	{
		$expected = <<<EOH
<embed src="movie.mov" type="video/quicktime" class="test" />

EOH;

		$embed = embed('movie.mov', 'video/quicktime', 'class="test"');
		$this->assertEquals($expected, $embed);
	}

	public function testObject()
	{
		$expected = <<<EOH
<object data="movie.swf" class="test"></object>

EOH;
		
		$object = object
		 (
			'movie.swf', 
			'application/x-shockwave-flash', 
			'class="test"'
		);

		$this->assertEquals($expected, $object);
		
		$expected = <<<EOH
<object data="movie.swf" class="test">
  <param name="foo" type="ref" value="bar" class="test" />
  <param name="hello" type="ref" value="world" class="test" />
</object>

EOH;
		
		$object = object
		(
			'movie.swf', 
			'application/x-shockwave-flash', 
			'class="test"',
			[
				param('foo', 'bar', 'ref', 'class="test"'),
				param('hello', 'world', 'ref', 'class="test"')
			]
		);
		$this->assertEquals($expected, $object);
	}

	// ------------------------------------------------------------------------

}