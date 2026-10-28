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

namespace CodeIgniter\HTTP;

use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;
use Config\Feature;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[BackupGlobals(true)]
#[Group('Others')]
final class NegotiateTest extends CIUnitTestCase
{
    private IncomingRequest $request;
    private Negotiate $negotiate;

    protected function setUp(): void
    {
        $this->resetServices();
        parent::setUp();

        $config          = new App();
        $this->request   = new IncomingRequest($config, new SiteURI($config), null, new UserAgent());
        $this->negotiate = new Negotiate($this->request);
    }

    public function testNegotiateMediaFindsHighestMatch(): void
    {
        $this->request->setHeader('Accept', 'text/plain; q=0.5, text/html, text/x-dvi; q=0.8, text/x-c');
        $this->negotiate->setRequest($this->request);

        $this->assertSame('text/html', $this->negotiate->media(['text/html', 'text/x-c', 'text/x-dvi', 'text/plain']));
        $this->assertSame('text/x-c', $this->negotiate->media(['text/x-c', 'text/x-dvi', 'text/plain']));
        $this->assertSame('text/x-dvi', $this->negotiate->media(['text/plain', 'text/x-dvi']));
        $this->assertSame('text/x-dvi', $this->negotiate->media(['text/x-dvi']));

        // No matches? Return the first that we support...
        $this->assertSame('text/md', $this->negotiate->media(['text/md']));
    }

    public function testParseHeaderDeterminesCorrectPrecedence(): void
    {
        $header = $this->negotiate->parseHeader('text/*, text/plain, text/plain;format=flowed, */*');

        $this->assertSame('text/plain', $header[0]['value']);
        $this->assertSame('flowed', $header[0]['params']['format']);
        $this->assertSame('text/plain', $header[1]['value']);
        $this->assertSame('*/*', $header[3]['value']);
    }

    public function testNegotiateMediaReturnsSupportedMatchWhenAsterisksInAvailable(): void
    {
        $this->request->setHeader('Accept', 'image/*, text/*');

        $this->assertSame('text/plain', $this->negotiate->media(['text/plain']));
    }

    public function testNegotiateMediaRecognizesMediaTypes(): void
    {
        // Image has a higher specificity, but is the wrong type...
        $this->request->setHeader('Accept', 'text/*, image/jpeg');

        $this->assertSame('text/plain', $this->negotiate->media(['text/plain']));
    }

    public function testNegotiateMediaSupportsStrictMatching(): void
    {
        // Image has a higher specificity, but is the wrong type...
        $this->request->setHeader('Accept', 'text/md, image/jpeg');

        $this->assertSame('text/plain', $this->negotiate->media(['text/plain']));
        $this->assertSame('', $this->negotiate->media(['text/plain'], true));
    }

    public function testAcceptCharsetMatchesBasics(): void
    {
        $this->request->setHeader('Accept-Charset', 'iso-8859-5, unicode-1-1;q=0.8');

        $this->assertSame('iso-8859-5', $this->negotiate->charset(['iso-8859-5', 'unicode-1-1']));
        $this->assertSame('unicode-1-1', $this->negotiate->charset(['utf-8', 'unicode-1-1']));

        // No match will default to utf-8
        $this->assertSame('utf-8', $this->negotiate->charset(['iso-8859', 'unicode-1-2']));
    }

    public function testNegotiateEncodingReturnsFirstIfNoAcceptHeaderExists(): void
    {
        $this->assertSame('compress', $this->negotiate->encoding(['compress', 'gzip']));
    }

    public function testNegotiatesEncodingBasics(): void
    {
        $this->request->setHeader('Accept-Encoding', 'gzip;q=1.0, identity; q=0.4, compress;q=0.5');

        $this->assertSame('gzip', $this->negotiate->encoding(['gzip', 'compress']));
        $this->assertSame('compress', $this->negotiate->encoding(['compress']));
        $this->assertSame('identity', $this->negotiate->encoding());
    }

    public function testAcceptLanguageBasics(): void
    {
        $this->request->setHeader('Accept-Language', 'da, en-gb, en-us;q=0.8, en;q=0.7');

        $this->assertSame('da', $this->negotiate->language(['da', 'en']));
        $this->assertSame('en-gb', $this->negotiate->language(['en-gb', 'en']));
        $this->assertSame('en', $this->negotiate->language(['en']));

        // Will find the first locale instead of "en-gb"
        $this->assertSame('en-us', $this->negotiate->language(['en-us', 'en-gb', 'en']));
        $this->assertSame('en', $this->negotiate->language(['en', 'en-us', 'en-gb']));

        config(Feature::class)->strictLocaleNegotiation = true;

        $this->assertSame('da', $this->negotiate->language(['da', 'en']));
        $this->assertSame('en-gb', $this->negotiate->language(['en-gb', 'en']));
        $this->assertSame('en', $this->negotiate->language(['en']));
        $this->assertSame('en-gb', $this->negotiate->language(['en-us', 'en-gb', 'en']));
        $this->assertSame('en-gb', $this->negotiate->language(['en', 'en-us', 'en-gb']));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/2774
     */
    public function testAcceptLanguageMatchesBroadly(): void
    {
        $this->request->setHeader('Accept-Language', 'fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7');

        $this->assertSame('fr', $this->negotiate->language(['fr', 'fr-FR', 'en']));
        $this->assertSame('fr-FR', $this->negotiate->language(['fr-FR', 'fr', 'en']));
        $this->assertSame('fr-BE', $this->negotiate->language(['fr-BE', 'fr', 'en']));
        $this->assertSame('en', $this->negotiate->language(['en', 'en-US']));
        $this->assertSame('fr-BE', $this->negotiate->language(['ru', 'en-GB', 'fr-BE']));

        config(Feature::class)->strictLocaleNegotiation = true;

        $this->assertSame('fr-FR', $this->negotiate->language(['fr', 'fr-FR', 'en']));
        $this->assertSame('fr-FR', $this->negotiate->language(['fr-FR', 'fr', 'en']));
        $this->assertSame('fr', $this->negotiate->language(['fr-BE', 'fr', 'en']));
        $this->assertSame('en-US', $this->negotiate->language(['en', 'en-US']));
        $this->assertSame('fr-BE', $this->negotiate->language(['ru', 'en-GB', 'fr-BE']));
    }

    public function testBestMatchEmpty(): void
    {
        $this->expectException(HTTPException::class);
        $this->negotiate->media([]);
    }

    public function testBestMatchNoHeader(): void
    {
        $this->request->setHeader('Accept', '');
        $this->assertSame('', $this->negotiate->media(['apple', 'banana'], true));
        $this->assertSame('apple/mac', $this->negotiate->media(['apple/mac', 'banana/yellow'], false));
    }

    public function testBestMatchNotAcceptable(): void
    {
        $this->request->setHeader('Accept', 'popcorn/cheddar');
        $this->assertSame('apple/mac', $this->negotiate->media(['apple/mac', 'banana/yellow'], false));
        $this->assertSame('banana/yellow', $this->negotiate->media(['banana/yellow', 'apple/mac'], false));
    }

    public function testBestMatchFirstSupported(): void
    {
        $this->request->setHeader('Accept', 'popcorn/cheddar, */*');
        $this->assertSame('apple/mac', $this->negotiate->media(['apple/mac', 'banana/yellow'], false));
    }

    public function testBestMatchLowQuality(): void
    {
        $this->request->setHeader('Accept', 'popcorn/cheddar;q=0, apple/mac, */*');
        $this->assertSame('apple/mac', $this->negotiate->media(['apple/mac', 'popcorn/cheddar'], false));
        $this->assertSame('apple/mac', $this->negotiate->media(['popcorn/cheddar', 'apple/mac'], false));
    }

    public function testWildcardsSkipExcludedValues(): void
    {
        $this->request->setHeader('Accept', 'application/json;q=0, */*');
        $this->assertSame('text/html', $this->negotiate->media(['application/json', 'text/html'], true));

        $this->request->setHeader('Accept', 'text/*, text/html;q=0');
        $this->assertSame('text/plain', $this->negotiate->media(['text/html', 'text/plain'], true));

        $this->request->setHeader('Accept', '*/*, text/html;q=0');
        $this->assertSame('', $this->negotiate->media(['text/html'], true));

        $this->request->setHeader('Accept-Charset', 'utf-8;q=0, *');
        $this->assertSame('iso-8859-1', $this->negotiate->charset(['utf-8', 'iso-8859-1']));

        $this->request->setHeader('Accept-Encoding', 'gzip;q=0, *');
        $this->assertSame('br', $this->negotiate->encoding(['gzip', 'br']));

        $this->request->setHeader('Accept-Language', 'en;q=0, *');
        $this->assertSame('fr', $this->negotiate->language(['en', 'fr']));

        config(Feature::class)->strictLocaleNegotiation = true;

        $this->assertSame('de', $this->negotiate->language(['en', 'de']));
    }

    public function testMediaRangesExcludeMatchingValues(): void
    {
        $this->request->setHeader('Accept', 'text/*;q=0, */*');
        $this->assertSame('application/json', $this->negotiate->media(['text/html', 'application/json'], true));

        $this->request->setHeader('Accept', 'text/html;q=0, */*');
        $this->assertSame('text/plain', $this->negotiate->media(['text/html;charset=utf-8', 'text/plain'], true));

        $this->request->setHeader('Accept', 'text/html;q=0, text/*');
        $this->assertSame('text/css', $this->negotiate->media(['text/html;charset=utf-8', 'text/css'], true));

        // a range only excludes the values that have its parameters
        $this->request->setHeader('Accept', 'text/html;level=1;q=0, */*');
        $this->assertSame('text/html', $this->negotiate->media(['text/html;level=1', 'text/html'], true));
    }

    public function testRangesExcludeValuesIgnoringCase(): void
    {
        $this->request->setHeader('Accept', 'text/*;q=0, TEXT/HTML;q=0.5, */*;q=0.1');
        $this->assertSame('text/html', $this->negotiate->media(['text/html'], true));

        $this->request->setHeader('Accept', 'TEXT/*;q=0, */*');
        $this->assertSame('application/json', $this->negotiate->media(['text/html', 'application/json'], true));

        $this->request->setHeader('Accept', 'text/html;CHARSET="UTF-8";q=0, */*');
        $this->assertSame('text/plain', $this->negotiate->media(['text/html;charset=utf-8', 'text/plain'], true));

        $this->request->setHeader('Accept-Charset', 'UTF-8;q=0, *');
        $this->assertSame('iso-8859-1', $this->negotiate->charset(['utf-8', 'iso-8859-1']));

        $this->request->setHeader('Accept-Encoding', 'GZIP;q=0, *');
        $this->assertSame('br', $this->negotiate->encoding(['gzip', 'br']));
    }

    public function testMediaRangeParametersAddSpecificity(): void
    {
        $this->request->setHeader('Accept', 'text/*;charset=utf-8;q=0, text/*');
        $this->assertSame(
            'text/plain;charset=iso-8859-1',
            $this->negotiate->media(['text/html;charset=utf-8', 'text/plain;charset=iso-8859-1'], true),
        );

        $this->request->setHeader('Accept', '*/*;charset=utf-8;q=0, */*');
        $this->assertSame('application/json', $this->negotiate->media(['text/html;charset=utf-8', 'application/json'], true));

        // a type/subtype range is more specific than a type/* range with more parameters
        $this->request->setHeader('Accept', 'text/html;q=0, text/*;charset=utf-8;level=1');
        $this->assertSame(
            'text/plain;charset=utf-8;level=1',
            $this->negotiate->media(['text/html;charset=utf-8;level=1', 'text/plain;charset=utf-8;level=1'], true),
        );
    }

    public function testMoreSpecificRangesTakePrecedence(): void
    {
        $this->request->setHeader('Accept', 'text/html, text/*;q=0');
        $this->assertSame('text/html', $this->negotiate->media(['text/plain', 'text/html'], true));
        $this->assertSame('', $this->negotiate->media(['text/plain'], true));

        $this->request->setHeader('Accept', 'text/html;q=0, text/html;level=1;q=0.5, */*');
        $this->assertSame('text/html;level=1', $this->negotiate->media(['text/html', 'text/html;level=1'], true));

        $this->request->setHeader('Accept-Language', 'en;q=0, en-GB;q=0.5, *');
        $this->assertSame('en-GB', $this->negotiate->language(['en-US', 'en-GB', 'fr']));

        config(Feature::class)->strictLocaleNegotiation = true;

        $this->assertSame('en-GB', $this->negotiate->language(['en-US', 'en-GB']));
    }

    public function testLanguageRangesExcludeRegionalLocales(): void
    {
        $this->request->setHeader('Accept-Language', 'en;q=0, *');
        $this->assertSame('fr', $this->negotiate->language(['en-US', 'fr']));

        // language ranges are case-insensitive and end on a subtag boundary
        $this->request->setHeader('Accept-Language', 'zh;q=0, *');
        $this->assertSame('zha', $this->negotiate->language(['ZH-Hant', 'zha']));

        $this->request->setHeader('Accept-Language', 'en-US, en-GB;q=0');
        $this->assertSame('en-AU', $this->negotiate->language(['en-GB', 'en-AU']));

        config(Feature::class)->strictLocaleNegotiation = true;

        $this->request->setHeader('Accept-Language', 'en;q=0, *');
        $this->assertSame('fr', $this->negotiate->language(['en-US', 'fr']));

        $this->request->setHeader('Accept-Language', 'zh;q=0, *');
        $this->assertSame('zha', $this->negotiate->language(['ZH-Hant', 'zha']));

        $this->request->setHeader('Accept-Language', 'en-US, en-GB;q=0');
        $this->assertSame('en-AU', $this->negotiate->language(['en-GB', 'en-AU']));
    }

    public function testBestMatchOnlyLowQuality(): void
    {
        $this->request->setHeader('Accept', 'popcorn/cheddar;q=0');
        // the first supported should be returned, since nothing will make us happy
        $this->assertSame('apple/mac', $this->negotiate->media(['apple/mac', 'popcorn/cheddar'], false));
        $this->assertSame('popcorn/cheddar', $this->negotiate->media(['popcorn/cheddar', 'apple/mac'], false));
    }

    public function testBestMatchIgnoresMediaRangesWithoutSubtype(): void
    {
        $this->request->setHeader('Accept', 'json, application/json');
        $this->assertSame('application/json', $this->negotiate->media(['text/html', 'application/json'], true));

        $this->request->setHeader('Accept', ', text/html');
        $this->assertSame('text/html', $this->negotiate->media(['application/json', 'text/html'], true));
    }

    public function testParameterMatching(): void
    {
        $this->request->setHeader('Accept', 'popcorn/cheddar;a=0;b=1');
        $this->assertSame('popcorn/cheddar;a=2', $this->negotiate->media(['popcorn/cheddar;a=2'], false));
        $this->assertSame('popcorn/cheddar;a=0', $this->negotiate->media(['popcorn/cheddar;a=0', 'popcorn/cheddar;a=2;b=1'], false));
    }
}
