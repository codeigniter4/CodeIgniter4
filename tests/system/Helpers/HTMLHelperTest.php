<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Helpers;

use CodeIgniter\Config\Factories;
use CodeIgniter\Files\Exceptions\FileNotFoundException;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;
use Config\DocTypes;

/**
 * @internal
 *
 * @group Others
 */
final class HTMLHelperTest extends CIUnitTestCase
{
    private array $tracks;

    /**
     * @var string Path to the test file for img_data
     */
    private string $imgPath = SUPPORTPATH . 'Images' . DIRECTORY_SEPARATOR . 'ci-logo.gif';

    /**
     * @var string Expected base64 encoding of img path
     */
    private string $imgData = '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';

    protected function setUp(): void
    {
        parent::setUp();

        $this->resetServices();

        helper('html');

        $this->tracks = [
            track('subtitles_no.vtt', 'subtitles', 'no', 'Norwegian No'),
            track('subtitles_yes.vtt', 'subtitles', 'yes', 'Norwegian Yes'),
        ];
    }

    public function testBasicUL(): void
    {
        $expected = <<<'EOH'
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

        $this->assertSame(ltrim($expected), ul($list));
    }

    public function testULWithClass(): void
    {
        $expected = <<<'EOH'
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

        $this->assertSame($expected, ul($list, 'class="test"'));
    }

    public function testMultiLevelUL(): void
    {
        $expected = <<<'EOH'
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

        $this->assertSame(ltrim($expected), ul($list));
    }

    public function testBasicOL(): void
    {
        $expected = <<<'EOH'
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

        $this->assertSame(ltrim($expected), ol($list));
    }

    public function testOLWithClass(): void
    {
        $expected = <<<'EOH'
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

        $this->assertSame($expected, ol($list, 'class="test"'));
    }

    public function testMultiLevelOL(): void
    {
        $expected = <<<'EOH'
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

        $this->assertSame(ltrim($expected), ol($list));
    }

    public function testIMG(): void
    {
        $target   = 'http://site.com/images/picture.jpg';
        $expected = '<img src="http://site.com/images/picture.jpg" alt="">';
        $this->assertSame($expected, img($target));
    }

    public function testIMGWithoutProtocol(): void
    {
        $target   = 'assets/mugshot.jpg';
        $expected = '<img src="http://example.com/assets/mugshot.jpg" alt="">';
        $this->assertSame($expected, img($target));
    }

    public function testIMGWithIndexpage(): void
    {
        $target   = 'assets/mugshot.jpg';
        $expected = '<img src="http://example.com/index.php/assets/mugshot.jpg" alt="">';
        $this->assertSame($expected, img($target, true));
    }

    public function testIMGXHTML(): void
    {
        $this->disableHtml5();

        $target   = 'http://site.com/images/picture.jpg';
        $expected = '<img src="http://site.com/images/picture.jpg" alt="" />';
        $this->assertSame($expected, img($target));

        $this->enableHtml5();
    }

    private function disableHtml5()
    {
        $doctypes        = new DocTypes();
        $doctypes->html5 = false;
        _solidus($doctypes);
    }

    private function enableHtml5()
    {
        $doctypes = new DocTypes();
        _solidus($doctypes);
    }

    public function testIMGXHTMLWithoutProtocol(): void
    {
        $this->disableHtml5();

        $target   = 'assets/mugshot.jpg';
        $expected = '<img src="http://example.com/assets/mugshot.jpg" alt="" />';
        $this->assertSame($expected, img($target));

        $this->enableHtml5();
    }

    public function testIMGXHTMLWithIndexpage(): void
    {
        $this->disableHtml5();

        $target   = 'assets/mugshot.jpg';
        $expected = '<img src="http://example.com/index.php/assets/mugshot.jpg" alt="" />';
        $this->assertSame($expected, img($target, true));

        $this->enableHtml5();
    }

    public function testImgData(): void
    {
        $expected = 'data:image/gif;base64,' . $this->imgData;

        $this->assertSame($expected, img_data($this->imgPath));
    }

    public function testImgDataWithMime(): void
    {
        $expected = 'data:image/png;base64,' . $this->imgData;

        $this->assertSame($expected, img_data($this->imgPath, 'image/png'));
    }

    public function testImgDataUnknownMime(): void
    {
        $path   = SUPPORTPATH . 'Validation' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'phpUxc0ty';
        $result = img_data($path);

        $this->assertSame(0, strpos($result, 'data:image/jpg'));
    }

    public function testImgDataNoFile(): void
    {
        $this->expectException(FileNotFoundException::class);

        img_data($this->imgPath . 'gobbledygook');
    }

    public function testScriptTag(): void
    {
        $target   = 'http://site.com/js/mystyles.js';
        $expected = '<script src="http://site.com/js/mystyles.js"></script>';
        $this->assertSame($expected, script_tag($target));
    }

    public function testScriptTagWithoutProtocol(): void
    {
        $target   = 'js/mystyles.js';
        $expected = '<script src="http://example.com/js/mystyles.js"></script>';
        $this->assertSame($expected, script_tag($target));
    }

    public function testScriptTagWithIndexpage(): void
    {
        $target   = 'js/mystyles.js';
        $expected = '<script src="http://example.com/index.php/js/mystyles.js"></script>';
        $this->assertSame($expected, script_tag($target, true));
    }

    public function testScriptTagWithSrc(): void
    {
        $target   = ['src' => 'http://site.com/js/mystyles.js'];
        $expected = '<script src="http://site.com/js/mystyles.js"></script>';
        $this->assertSame($expected, script_tag($target));
    }

    public function testScriptTagWithSrcWithoutProtocol(): void
    {
        $target   = ['src' => 'js/mystyles.js'];
        $expected = '<script src="http://example.com/js/mystyles.js"></script>';
        $this->assertSame($expected, script_tag($target));
    }

    public function testScriptTagWithSrcAndAttributes(): void
    {
        $target   = ['src' => 'js/mystyles.js', 'charset' => 'UTF-8', 'defer' => '', 'async' => null];
        $expected = '<script src="http://example.com/js/mystyles.js" charset="UTF-8" defer="" async></script>';
        $this->assertSame($expected, script_tag($target));
    }

    public function testScriptTagWithCsp(): void
    {
        // Reset CSP object
        $this->resetServices();

        $config             = new App();
        $config->CSPEnabled = true;
        Factories::injectMock('config', 'App', $config);

        $target = 'http://site.com/js/mystyles.js';
        $html   = script_tag($target);

        $this->assertMatchesRegularExpression(
            '!<script nonce="\w+?" src="http://site.com/js/mystyles.js".*?>!u',
            $html
        );

        // Reset CSP object
        $this->resetFactories();
        $this->resetServices();
    }

    /**
     * This test has probably no real-world value but may help detecting
     * a change in the default behaviour.
     */
    public function testScriptTagWithoutAnyArg(): void
    {
        $expected = '<script src="http://example.com/"></script>';
        $this->assertSame($expected, script_tag());
    }

    public function testLinkTag(): void
    {
        $target   = 'css/mystyles.css';
        $expected = '<link href="http://example.com/css/mystyles.css" rel="stylesheet" type="text/css">';
        $this->assertSame($expected, link_tag($target));
    }

    public function testLinkTagXHTML(): void
    {
        $this->disableHtml5();

        $target   = 'css/mystyles.css';
        $expected = '<link href="http://example.com/css/mystyles.css" rel="stylesheet" type="text/css" />';
        $this->assertSame($expected, link_tag($target));

        $this->enableHtml5();
    }

    public function testLinkTagMedia(): void
    {
        $target = 'https://styles.com/css/mystyles.css';
        $tag    = link_tag($target, 'stylesheet', 'text/css', '', 'print');

        $expected = '<link href="https://styles.com/css/mystyles.css" rel="stylesheet" type="text/css" media="print">';
        $this->assertSame($expected, $tag);
    }

    public function testLinkTagTitle(): void
    {
        $tag = link_tag('default.css', 'stylesheet', 'text/css', 'Default Style');

        $expected = '<link href="http://example.com/default.css" rel="stylesheet" type="text/css" title="Default Style">';
        $this->assertSame($expected, $tag);
    }

    public function testLinkTagFavicon(): void
    {
        $tag = link_tag('favicon.ico', 'shortcut icon', 'image/ico');

        $expected = '<link href="http://example.com/favicon.ico" rel="shortcut icon" type="image/ico">';
        $this->assertSame($expected, $tag);
    }

    public function testLinkTagRss(): void
    {
        $tag = link_tag('feed', 'alternate', 'application/rss+xml', 'My RSS Feed');

        $expected = '<link href="http://example.com/feed" rel="alternate" type="application/rss+xml" title="My RSS Feed">';
        $this->assertSame($expected, $tag);
    }

    public function testLinkTagAlternate(): void
    {
        $tag = link_tag(
            'http://sp.example.com/',
            'alternate',
            '',
            '',
            'only screen and (max-width: 640px)'
        );

        $expected = '<link href="http://sp.example.com/" rel="alternate" media="only screen and (max-width: 640px)">';
        $this->assertSame($expected, $tag);
    }

    public function testLinkTagArrayAlternate(): void
    {
        $tag = link_tag([
            'href'  => 'http://sp.example.com/',
            'rel'   => 'alternate',
            'media' => 'only screen and (max-width: 640px)',
        ]);

        $expected = '<link href="http://sp.example.com/" rel="alternate" media="only screen and (max-width: 640px)">';
        $this->assertSame($expected, $tag);
    }

    public function testLinkTagCanonical(): void
    {
        $tag = link_tag('http://www.example.com/', 'canonical');

        $expected = '<link href="http://www.example.com/" rel="canonical">';
        $this->assertSame($expected, $tag);
    }

    public function testLinkTagArray(): void
    {
        $parms = [
            'href'      => 'css/mystyles.css',
            'indexPage' => true,
        ];
        $expected = '<link href="http://example.com/index.php/css/mystyles.css" rel="stylesheet" type="text/css">';
        $this->assertSame($expected, link_tag($parms));
    }

    public function testLinkTagArrayHreflang(): void
    {
        $tag = link_tag([
            'href'     => 'https://example.com/en',
            'rel'      => 'alternate',
            'hreflang' => 'x-default',
        ]);

        $expected = '<link href="https://example.com/en" hreflang="x-default" rel="alternate">';
        $this->assertSame($expected, $tag);
    }

    public function testDocType(): void
    {
        $target   = 'html4-strict';
        $expected = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';
        $this->assertSame($expected, doctype($target));
    }

    public function testDocTypeDefault(): void
    {
        $expected = '<!DOCTYPE html>';
        $this->assertSame($expected, doctype());
    }

    public function testDocTypeInvalid(): void
    {
        $target = 'good-guess';
        $this->assertEmpty(doctype($target));
    }

    public function testVideo(): void
    {
        $expected = <<<'EOH'
            <video src="http://www.codeigniter.com/test.mp4" controls>
              Your browser does not support the video tag.
            </video>

            EOH;

        $target  = 'http://www.codeigniter.com/test.mp4';
        $message = 'Your browser does not support the video tag.';
        $video   = video($target, $message, 'controls');
        $this->assertSame($expected, $video);
    }

    public function testVideoWithTracks(): void
    {
        $expected = <<<'EOH'
            <video src="http://example.com/test.mp4" controls>
              <track src="subtitles_no.vtt" kind="subtitles" srclang="no" label="Norwegian No">
              <track src="subtitles_yes.vtt" kind="subtitles" srclang="yes" label="Norwegian Yes">
              Your browser does not support the video tag.
            </video>

            EOH;

        $target  = 'test.mp4';
        $message = 'Your browser does not support the video tag.';
        $video   = video($target, $message, 'controls', $this->tracks);
        $this->assertSame($expected, $video);
    }

    public function testVideoWithTracksXHTML(): void
    {
        $this->disableHtml5();

        $expected = <<<'EOH'
            <video src="http://example.com/test.mp4" controls>
              <track src="subtitles_no.vtt" kind="subtitles" srclang="no" label="Norwegian No" />
              <track src="subtitles_yes.vtt" kind="subtitles" srclang="yes" label="Norwegian Yes" />
              Your browser does not support the video tag.
            </video>

            EOH;

        $target  = 'test.mp4';
        $message = 'Your browser does not support the video tag.';
        $tracks  = [
            track('subtitles_no.vtt', 'subtitles', 'no', 'Norwegian No'),
            track('subtitles_yes.vtt', 'subtitles', 'yes', 'Norwegian Yes'),
        ];
        $video = video($target, $message, 'controls', $tracks);
        $this->assertSame($expected, $video);

        $this->enableHtml5();
    }

    public function testVideoWithTracksAndIndex(): void
    {
        $expected = <<<'EOH'
            <video src="http://example.com/index.php/test.mp4" controls>
              <track src="subtitles_no.vtt" kind="subtitles" srclang="no" label="Norwegian No">
              <track src="subtitles_yes.vtt" kind="subtitles" srclang="yes" label="Norwegian Yes">
              Your browser does not support the video tag.
            </video>

            EOH;

        $target  = 'test.mp4';
        $message = 'Your browser does not support the video tag.';
        $video   = video($target, $message, 'controls', $this->tracks, true);
        $this->assertSame($expected, $video);
    }

    public function testVideoMultipleSources(): void
    {
        $expected = <<<'EOH'
            <video class="test" controls>
              <source src="http://example.com/movie.mp4" type="video/mp4" class="test">
              <source src="http://example.com/movie.ogg" type="video/ogg">
              <source src="http://example.com/movie.mov" type="video/quicktime">
              <source src="http://example.com/movie.ogv" type="video/ogv; codecs=dirac, speex">
              <track src="subtitles_no.vtt" kind="subtitles" srclang="no" label="Norwegian No">
              <track src="subtitles_yes.vtt" kind="subtitles" srclang="yes" label="Norwegian Yes">
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

        $this->assertSame($expected, $video);
    }

    public function testVideoMultipleSourcesXHTML(): void
    {
        $this->disableHtml5();

        $expected = <<<'EOH'
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
        $tracks  = [
            track('subtitles_no.vtt', 'subtitles', 'no', 'Norwegian No'),
            track('subtitles_yes.vtt', 'subtitles', 'yes', 'Norwegian Yes'),
        ];
        $video = video($sources, $message, 'class="test" controls', $tracks);

        $this->assertSame($expected, $video);

        $this->enableHtml5();
    }

    public function testAudio(): void
    {
        $expected = <<<'EOH'
            <audio id="test" controls>
              <source src="http://example.com/sound.ogg" type="audio/ogg">
              <source src="http://example.com/sound.mpeg" type="audio/mpeg">
              <track src="subtitles_no.vtt" kind="subtitles" srclang="no" label="Norwegian No">
              <track src="subtitles_yes.vtt" kind="subtitles" srclang="yes" label="Norwegian Yes">
              Your browser does not support the audio tag.
            </audio>

            EOH;

        $sources = [
            source('sound.ogg', 'audio/ogg'),
            source('sound.mpeg', 'audio/mpeg'),
        ];
        $message = 'Your browser does not support the audio tag.';
        $audio   = audio($sources, $message, 'id="test" controls', $this->tracks);

        $this->assertSame($expected, $audio);
    }

    public function testAudioXHTML(): void
    {
        $this->disableHtml5();

        $expected = <<<'EOH'
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
        $tracks = [
            track('subtitles_no.vtt', 'subtitles', 'no', 'Norwegian No'),
            track('subtitles_yes.vtt', 'subtitles', 'yes', 'Norwegian Yes'),
        ];
        $message = 'Your browser does not support the audio tag.';
        $audio   = audio($sources, $message, 'id="test" controls', $tracks);

        $this->assertSame($expected, $audio);

        $this->enableHtml5();
    }

    public function testAudioSimple(): void
    {
        $expected = <<<'EOH'
            <audio src="http://example.com/sound.mpeg" type="audio/mpeg" id="test" controls>
              Your browser does not support the audio tag.
            </audio>

            EOH;

        $source  = 'sound.mpeg';
        $message = 'Your browser does not support the audio tag.';
        $audio   = audio($source, $message, 'type="audio/mpeg" id="test" controls');

        $this->assertSame($expected, $audio);
    }

    public function testAudioWithSource(): void
    {
        $expected = <<<'EOH'
            <audio src="http://codeigniter.com/sound.mpeg" type="audio/mpeg" id="test" controls>
              Your browser does not support the audio tag.
            </audio>

            EOH;

        $source  = 'http://codeigniter.com/sound.mpeg';
        $message = 'Your browser does not support the audio tag.';
        $audio   = audio($source, $message, 'type="audio/mpeg" id="test" controls');

        $this->assertSame($expected, $audio);
    }

    public function testAudioWithIndex(): void
    {
        $expected = <<<'EOH'
            <audio src="http://example.com/index.php/sound.mpeg" type="audio/mpeg" id="test" controls>
              Your browser does not support the audio tag.
            </audio>

            EOH;

        $source  = 'sound.mpeg';
        $message = 'Your browser does not support the audio tag.';
        $audio   = audio($source, $message, 'type="audio/mpeg" id="test" controls', [], true);

        $this->assertSame($expected, $audio);
    }

    public function testAudioWithTracks(): void
    {
        $expected = <<<'EOH'
            <audio src="http://example.com/sound.mpeg" type="audio/mpeg" id="test" controls>
              <track src="subtitles_no.vtt" kind="subtitles" srclang="no" label="Norwegian No">
              <track src="subtitles_yes.vtt" kind="subtitles" srclang="yes" label="Norwegian Yes">
              Your browser does not support the audio tag.
            </audio>

            EOH;

        $source  = 'sound.mpeg';
        $message = 'Your browser does not support the audio tag.';
        $audio   = audio($source, $message, 'type="audio/mpeg" id="test" controls', $this->tracks);

        $this->assertSame($expected, $audio);
    }

    public function testMediaNameOnly(): void
    {
        $expected = <<<'EOH'
            <av>
            </av>

            EOH;
        $this->assertSame($expected, _media('av'));
    }

    public function testMediaWithSources(): void
    {
        $expected = <<<'EOH'
            <av>
              <source src="http://example.com/sound.ogg" type="audio/ogg">
              <source src="http://example.com/sound.mpeg" type="audio/mpeg">
            </av>

            EOH;
        $sources = [
            source('sound.ogg', 'audio/ogg'),
            source('sound.mpeg', 'audio/mpeg'),
        ];
        $this->assertSame($expected, _media('av', $sources));
    }

    public function testSource(): void
    {
        $expected = '<source src="http://example.com/index.php/sound.mpeg" type="audio/mpeg">';
        $this->assertSame($expected, source('sound.mpeg', 'audio/mpeg', '', true));
    }

    public function testSourceXHTML(): void
    {
        $this->disableHtml5();

        $expected = '<source src="http://example.com/index.php/sound.mpeg" type="audio/mpeg" />';
        $this->assertSame($expected, source('sound.mpeg', 'audio/mpeg', '', true));

        $this->enableHtml5();
    }

    public function testEmbed(): void
    {
        $expected = <<<'EOH'
            <embed src="http://example.com/movie.mov" type="video/quicktime" class="test">

            EOH;

        $type  = 'video/quicktime';
        $embed = embed('movie.mov', $type, 'class="test"');
        $this->assertSame($expected, $embed);
    }

    public function testEmbedXHTML(): void
    {
        $this->disableHtml5();

        $expected = <<<'EOH'
            <embed src="http://example.com/movie.mov" type="video/quicktime" class="test" />

            EOH;

        $type  = 'video/quicktime';
        $embed = embed('movie.mov', $type, 'class="test"');
        $this->assertSame($expected, $embed);

        $this->enableHtml5();
    }

    public function testEmbedIndexed(): void
    {
        $expected = <<<'EOH'
            <embed src="http://example.com/index.php/movie.mov" type="video/quicktime" class="test">

            EOH;

        $type  = 'video/quicktime';
        $embed = embed('movie.mov', $type, 'class="test"', true);
        $this->assertSame($expected, $embed, '');
    }

    public function testObject(): void
    {
        $expected = <<<'EOH'
            <object data="http://example.com/movie.swf" class="test"></object>

            EOH;

        $type   = 'application/x-shockwave-flash';
        $object = object('movie.swf', $type, 'class="test"');

        $this->assertSame($expected, $object);
    }

    public function testObjectWithParams(): void
    {
        $expected = <<<'EOH'
            <object data="http://example.com/movie.swf" class="test">
              <param name="foo" type="ref" value="bar" class="test">
              <param name="hello" type="ref" value="world" class="test">
            </object>

            EOH;

        $type  = 'application/x-shockwave-flash';
        $parms = [
            param('foo', 'bar', 'ref', 'class="test"'),
            param('hello', 'world', 'ref', 'class="test"'),
        ];
        $object = object('movie.swf', $type, 'class="test"', $parms);
        $this->assertSame($expected, $object);
    }

    public function testObjectWithParamsXHTML(): void
    {
        $this->disableHtml5();

        $expected = <<<'EOH'
            <object data="http://example.com/movie.swf" class="test">
              <param name="foo" type="ref" value="bar" class="test" />
              <param name="hello" type="ref" value="world" class="test" />
            </object>

            EOH;

        $type  = 'application/x-shockwave-flash';
        $parms = [
            param('foo', 'bar', 'ref', 'class="test"'),
            param('hello', 'world', 'ref', 'class="test"'),
        ];
        $object = object('movie.swf', $type, 'class="test"', $parms);
        $this->assertSame($expected, $object);

        $this->enableHtml5();
    }

    public function testObjectIndexed(): void
    {
        $expected = <<<'EOH'
            <object data="http://example.com/index.php/movie.swf" class="test"></object>

            EOH;

        $type   = 'application/x-shockwave-flash';
        $object = object('movie.swf', $type, 'class="test"', [], true);

        $this->assertSame($expected, $object);
    }
}
