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

use BadMethodCallException;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;

/**
 * @backupGlobals enabled
 *
 * @internal
 *
 * @group Others
 */
final class SiteURITest extends CIUnitTestCase
{
    public function testConstructor()
    {
        $config = new App();

        $uri = new SiteURI($config);

        $this->assertInstanceOf(SiteURI::class, $uri);
        $this->assertSame('http://example.com/index.php/', (string) $uri);
        $this->assertSame('/index.php/', $uri->getPath());
    }

    public function testConstructorSubfolder()
    {
        $config          = new App();
        $config->baseURL = 'http://example.com/ci4/';

        $uri = new SiteURI($config);

        $this->assertInstanceOf(SiteURI::class, $uri);
        $this->assertSame('http://example.com/ci4/index.php/', (string) $uri);
        $this->assertSame('/ci4/index.php/', $uri->getPath());
    }

    public function testConstructorForceGlobalSecureRequests()
    {
        $config                            = new App();
        $config->forceGlobalSecureRequests = true;

        $uri = new SiteURI($config);

        $this->assertSame('https://example.com/index.php/', (string) $uri);
    }

    public function testConstructorIndexPageEmpty()
    {
        $config            = new App();
        $config->indexPage = '';

        $uri = new SiteURI($config);

        $this->assertSame('http://example.com/', (string) $uri);
    }

    public function testSetPath()
    {
        $config = new App();

        $uri = new SiteURI($config);

        $uri->setPath('test/method');

        $this->assertSame('http://example.com/index.php/test/method', (string) $uri);
        $this->assertSame('test/method', $uri->getRoutePath());
        $this->assertSame('/index.php/test/method', $uri->getPath());
        $this->assertSame(['test', 'method'], $uri->getSegments());
        $this->assertSame('test', $uri->getSegment(1));
        $this->assertSame(2, $uri->getTotalSegments());
    }

    public function testSetPathSubfolder()
    {
        $config          = new App();
        $config->baseURL = 'http://example.com/ci4/';

        $uri = new SiteURI($config);

        $uri->setPath('test/method');

        $this->assertSame('http://example.com/ci4/index.php/test/method', (string) $uri);
        $this->assertSame('test/method', $uri->getRoutePath());
        $this->assertSame('/ci4/index.php/test/method', $uri->getPath());
        $this->assertSame(['test', 'method'], $uri->getSegments());
        $this->assertSame('test', $uri->getSegment(1));
        $this->assertSame(2, $uri->getTotalSegments());
    }

    public function testSetPathEmpty()
    {
        $config = new App();

        $uri = new SiteURI($config);

        $uri->setPath('');

        $this->assertSame('http://example.com/index.php/', (string) $uri);
        $this->assertSame('/', $uri->getRoutePath());
        $this->assertSame('/index.php/', $uri->getPath());
        $this->assertSame([], $uri->getSegments());
        $this->assertSame(0, $uri->getTotalSegments());
    }

    public function testSetSegment()
    {
        $config = new App();

        $uri = new SiteURI($config);
        $uri->setPath('test/method');

        $uri->setSegment(1, 'one');

        $this->assertSame('http://example.com/index.php/one/method', (string) $uri);
        $this->assertSame('one/method', $uri->getRoutePath());
        $this->assertSame('/index.php/one/method', $uri->getPath());
        $this->assertSame(['one', 'method'], $uri->getSegments());
        $this->assertSame('one', $uri->getSegment(1));
        $this->assertSame(2, $uri->getTotalSegments());
    }

    public function testSetSegmentOutOfRange()
    {
        $this->expectException(HTTPException::class);

        $config = new App();
        $uri    = new SiteURI($config);
        $uri->setPath('test/method');

        $uri->setSegment(4, 'four');
    }

    public function testSetSegmentSilentOutOfRange()
    {
        $config = new App();
        $uri    = new SiteURI($config);
        $uri->setPath('one/method');
        $uri->setSilent();

        $uri->setSegment(4, 'four');
        $this->assertSame(['one', 'method'], $uri->getSegments());
    }

    public function testSetSegmentZero()
    {
        $this->expectException(HTTPException::class);

        $config = new App();
        $uri    = new SiteURI($config);
        $uri->setPath('test/method');

        $uri->setSegment(0, 'four');
    }

    public function testSetSegmentSubfolder()
    {
        $config          = new App();
        $config->baseURL = 'http://example.com/ci4/';

        $uri = new SiteURI($config);
        $uri->setPath('test/method');

        $uri->setSegment(1, 'one');

        $this->assertSame('http://example.com/ci4/index.php/one/method', (string) $uri);
        $this->assertSame('one/method', $uri->getRoutePath());
        $this->assertSame('/ci4/index.php/one/method', $uri->getPath());
        $this->assertSame(['one', 'method'], $uri->getSegments());
        $this->assertSame('one', $uri->getSegment(1));
        $this->assertSame(2, $uri->getTotalSegments());
    }

    public function testGetRoutePath()
    {
        $config = new App();
        $uri    = new SiteURI($config);

        $this->assertSame('/', $uri->getRoutePath());
    }

    public function testGetSegments()
    {
        $config = new App();
        $uri    = new SiteURI($config);

        $this->assertSame([], $uri->getSegments());
    }

    public function testGetSegmentZero()
    {
        $this->expectException(HTTPException::class);

        $config = new App();
        $uri    = new SiteURI($config);
        $uri->setPath('test/method');

        $uri->getSegment(0);
    }

    public function testGetSegmentOutOfRange()
    {
        $this->expectException(HTTPException::class);

        $config = new App();
        $uri    = new SiteURI($config);
        $uri->setPath('test/method');

        $this->assertSame('method', $uri->getSegment(2));
        $this->assertSame('', $uri->getSegment(3));

        $uri->getSegment(4);
    }

    public function testGetTotalSegments()
    {
        $config = new App();
        $uri    = new SiteURI($config);

        $this->assertSame(0, $uri->getTotalSegments());
    }

    public function testSetURI()
    {
        $this->expectException(BadMethodCallException::class);

        $config = new App();
        $uri    = new SiteURI($config);

        $uri->setURI('http://another.site.example.jp/');
    }
}
