<?php

namespace CodeIgniter\HTTP;

use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;

/**
 * @backupGlobals enabled
 *
 * @internal
 */
final class IncomingRequestTest extends CIUnitTestCase
{
    /**
     * @var IncomingRequest
     */
    protected $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = new IncomingRequest(new App(), new URI(), null, new UserAgent());

        $_POST = $_GET = $_SERVER = $_REQUEST = $_ENV = $_COOKIE = $_SESSION = [];
    }

    //--------------------------------------------------------------------

    public function testCanGrabRequestVars()
    {
        $_REQUEST['TEST'] = 5;

        $this->assertSame('5', $this->request->getVar('TEST'));
        $this->assertNull($this->request->getVar('TESTY'));
    }

    public function testCanGrabGetVars()
    {
        $_GET['TEST'] = 5;

        $this->assertSame('5', $this->request->getGet('TEST'));
        $this->assertNull($this->request->getGet('TESTY'));
    }

    public function testCanGrabPostVars()
    {
        $_POST['TEST'] = 5;

        $this->assertSame('5', $this->request->getPost('TEST'));
        $this->assertNull($this->request->getPost('TESTY'));
    }

    public function testCanGrabPostBeforeGet()
    {
        $_POST['TEST'] = 5;
        $_GET['TEST']  = 3;

        $this->assertSame('5', $this->request->getPostGet('TEST'));
        $this->assertSame('3', $this->request->getGetPost('TEST'));
    }

    //--------------------------------------------------------------------

    public function testNoOldInput()
    {
        $this->assertNull($this->request->getOldInput('name'));
    }

    public function testCanGetOldInput()
    {
        $_SESSION['_ci_old_input'] = [
            'get'  => ['one' => 'two'],
            'post' => ['name' => 'foo'],
        ];

        $this->assertSame('foo', $this->request->getOldInput('name'));
        $this->assertSame('two', $this->request->getOldInput('one'));
    }

    public function testCanGetOldInputDotted()
    {
        $_SESSION['_ci_old_input'] = [
            'get'  => ['apple' => ['name' => 'two']],
            'post' => ['banana' => ['name' => 'foo']],
        ];

        $this->assertSame('foo', $this->request->getOldInput('banana.name'));
        $this->assertSame('two', $this->request->getOldInput('apple.name'));
    }

    public function testMissingOldInput()
    {
        $_SESSION['_ci_old_input'] = [
            'get'  => ['apple' => ['name' => 'two']],
            'post' => ['banana' => ['name' => 'foo']],
        ];

        $this->assertNull($this->request->getOldInput('pineapple.name'));
    }

    // Reference: https://github.com/codeigniter4/CodeIgniter4/issues/1492
    public function testCanGetOldInputArray()
    {
        $_SESSION['_ci_old_input'] = [
            'get'  => ['apple' => ['name' => 'two']],
            'post' => ['banana' => ['name' => 'foo']],
        ];

        $this->assertSame(['name' => 'two'], $this->request->getOldInput('apple'));
        $this->assertSame(['name' => 'foo'], $this->request->getOldInput('banana'));
    }

    // Reference: https://github.com/codeigniter4/CodeIgniter4/issues/1492

    /**
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function testCanSerializeOldArray()
    {
        $locations = [
            'AB' => 'Alberta',
            'BC' => 'British Columbia',
            'SK' => 'Saskatchewan',
        ];
        $session = service('session');
        $session->set(['_ci_old_input' => ['post' => ['location' => $locations]]]);

        $this->assertSame($locations, $this->request->getOldInput('location'));
    }

    //--------------------------------------------------------------------

    public function testCanGrabServerVars()
    {
        $server                   = $this->getPrivateProperty($this->request, 'globals');
        $server['server']['TEST'] = 5;
        $this->setPrivateProperty($this->request, 'globals', $server);

        $this->assertSame('5', $this->request->getServer('TEST'));
        $this->assertNull($this->request->getServer('TESTY'));
    }

    public function testCanGrabEnvVars()
    {
        $server                = $this->getPrivateProperty($this->request, 'globals');
        $server['env']['TEST'] = 5;
        $this->setPrivateProperty($this->request, 'globals', $server);

        $this->assertSame('5', $this->request->getEnv('TEST'));
        $this->assertNull($this->request->getEnv('TESTY'));
    }

    public function testCanGrabCookieVars()
    {
        $_COOKIE['TEST'] = 5;

        $this->assertSame('5', $this->request->getCookie('TEST'));
        $this->assertNull($this->request->getCookie('TESTY'));
    }

    //--------------------------------------------------------------------

    public function testStoresDefaultLocale()
    {
        $config = new App();

        $this->assertSame($config->defaultLocale, $this->request->getDefaultLocale());
        $this->assertSame($config->defaultLocale, $this->request->getLocale());
    }

    public function testSetLocaleSaves()
    {
        $config                   = new App();
        $config->supportedLocales = ['en', 'es'];
        $config->defaultLocale    = 'es';
        $config->baseURL          = 'http://example.com/';

        $request = new IncomingRequest($config, new URI(), null, new UserAgent());

        $request->setLocale('en');
        $this->assertSame('en', $request->getLocale());
    }

    public function testSetBadLocale()
    {
        $config                   = new App();
        $config->supportedLocales = ['en', 'es'];
        $config->defaultLocale    = 'es';
        $config->baseURL          = 'http://example.com/';

        $request = new IncomingRequest($config, new URI(), null, new UserAgent());

        $request->setLocale('xx');
        $this->assertSame('es', $request->getLocale());
    }

    //--------------------------------------------------------------------

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/2774
     */
    public function testNegotiatesLocale()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'fr-FR; q=1.0, en; q=0.5';

        $config                   = new App();
        $config->negotiateLocale  = true;
        $config->supportedLocales = ['fr', 'en'];
        $config->baseURL          = 'http://example.com/';

        $request = new IncomingRequest($config, new URI(), null, new UserAgent());

        $this->assertSame($config->defaultLocale, $request->getDefaultLocale());
        $this->assertSame('fr', $request->getLocale());
    }

    public function testNegotiatesLocaleOnlyBroad()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'fr; q=1.0, en; q=0.5';

        $config                   = new App();
        $config->negotiateLocale  = true;
        $config->supportedLocales = ['fr', 'en'];
        $config->baseURL          = 'http://example.com/';

        $request = new IncomingRequest($config, new URI(), null, new UserAgent());

        $this->assertSame($config->defaultLocale, $request->getDefaultLocale());
        $this->assertSame('fr', $request->getLocale());
    }

    // The negotiation tests below are not intended to exercise the HTTP\Negotiate class -
    // that is up to the NegotiateTest. These are only to make sure that the requests
    // flow through to the negotiator

    public function testNegotiatesNot()
    {
        $this->request->setHeader('Accept-Charset', 'iso-8859-5, unicode-1-1;q=0.8');

        $this->expectException(HTTPException::class);
        $this->request->negotiate('something bogus', ['iso-8859-5', 'unicode-1-1']);
    }

    public function testNegotiatesCharset()
    {
        //      $_SERVER['HTTP_ACCEPT_CHARSET'] = 'iso-8859-5, unicode-1-1;q=0.8';
        $this->request->setHeader('Accept-Charset', 'iso-8859-5, unicode-1-1;q=0.8');

        $this->assertSame(strtolower($this->request->config->charset), $this->request->negotiate('charset', ['iso-8859', 'unicode-1-2']));
    }

    public function testNegotiatesMedia()
    {
        $this->request->setHeader('Accept', 'text/plain; q=0.5, text/html, text/x-dvi; q=0.8, text/x-c');
        $this->assertSame('text/html', $this->request->negotiate('media', ['text/html', 'text/x-c', 'text/x-dvi', 'text/plain']));
    }

    public function testNegotiatesEncoding()
    {
        $this->request->setHeader('Accept-Encoding', 'gzip;q=1.0, identity; q=0.4, compress;q=0.5');
        $this->assertSame('gzip', $this->request->negotiate('encoding', ['gzip', 'compress']));
    }

    public function testNegotiatesLanguage()
    {
        $this->request->setHeader('Accept-Language', 'da, en-gb;q=0.8, en;q=0.7');
        $this->assertSame('en', $this->request->negotiate('language', ['en', 'da']));
    }

    //--------------------------------------------------------------------

    public function testCanGrabGetRawJSON()
    {
        $json = '{"code":1, "message":"ok"}';

        $expected = ['code' => 1, 'message' => 'ok'];

        $config          = new App();
        $config->baseURL = 'http://example.com/';

        $request = new IncomingRequest($config, new URI(), $json, new UserAgent());

        $this->assertSame($expected, $request->getJSON(true));
    }

    public function testCanGetAVariableFromJson()
    {
        $jsonObj = [
            'foo' => 'bar',
            'baz' => ['fizz' => 'buzz'],
        ];
        $json = json_encode($jsonObj);

        $config          = new App();
        $config->baseURL = 'http://example.com/';

        $request = new IncomingRequest($config, new URI(), $json, new UserAgent());

        $this->assertSame('bar', $request->getJsonVar('foo'));
        $jsonVar = $request->getJsonVar('baz');
        $this->assertIsObject($jsonVar);
        $this->assertSame('buzz', $jsonVar->fizz);
        $this->assertSame('buzz', $request->getJsonVar('baz.fizz'));
    }

    public function testGetJsonVarAsArray()
    {
        $jsonObj = [
            'baz' => [
                'fizz' => 'buzz',
                'foo'  => 'bar',
            ],
        ];
        $json = json_encode($jsonObj);

        $config          = new App();
        $config->baseURL = 'http://example.com/';

        $request = new IncomingRequest($config, new URI(), $json, new UserAgent());

        $jsonVar = $request->getJsonVar('baz', true);
        $this->assertIsArray($jsonVar);
        $this->assertSame('buzz', $jsonVar['fizz']);
        $this->assertSame('bar', $jsonVar['foo']);
    }

    public function testGetJsonVarCanFilter()
    {
        $json = json_encode(['foo' => 'bar']);

        $config          = new App();
        $config->baseURL = 'http://example.com/';

        $request = new IncomingRequest($config, new URI(), $json, new UserAgent());

        $this->assertFalse($request->getJsonVar('foo', false, FILTER_VALIDATE_INT));
    }

    public function testGetVarWorksWithJson()
    {
        $jsonObj = [
            'foo'  => 'bar',
            'fizz' => 'buzz',
        ];
        $json = json_encode($jsonObj);

        $config          = new App();
        $config->baseURL = 'http://example.com/';

        $request = new IncomingRequest($config, new URI(), $json, new UserAgent());
        $request->setHeader('Content-Type', 'application/json');

        $this->assertSame('bar', $request->getVar('foo'));
        $this->assertSame('buzz', $request->getVar('fizz'));

        $multiple = $request->getVar(['foo', 'fizz']);
        $this->assertIsArray($multiple);
        $this->assertSame('bar', $multiple['foo']);
        $this->assertSame('buzz', $multiple['fizz']);

        $all = $request->getVar();
        $this->assertIsObject($all);
        $this->assertSame('bar', $all->foo);
        $this->assertSame('buzz', $all->fizz);
    }

    public function testGetVarWorksWithJsonAndGetParams()
    {
        $config          = new App();
        $config->baseURL = 'http://example.com/';

        // GET method
        $_REQUEST['foo']  = 'bar';
        $_REQUEST['fizz'] = 'buzz';

        $request = new IncomingRequest($config, new URI('http://example.com/path?foo=bar&fizz=buzz'), 'php://input', new UserAgent());
        $request = $request->withMethod('GET');

        // JSON type
        $request->setHeader('Content-Type', 'application/json');

        $this->assertSame('bar', $request->getVar('foo'));
        $this->assertSame('buzz', $request->getVar('fizz'));

        $multiple = $request->getVar(['foo', 'fizz']);
        $this->assertIsArray($multiple);
        $this->assertSame('bar', $multiple['foo']);
        $this->assertSame('buzz', $multiple['fizz']);

        $all = $request->getVar();
        $this->assertIsArray($all);
        $this->assertSame('bar', $all['foo']);
        $this->assertSame('buzz', $all['fizz']);
    }

    public function testCanGrabGetRawInput()
    {
        $rawstring = 'username=admin001&role=administrator&usepass=0';

        $expected = [
            'username' => 'admin001',
            'role'     => 'administrator',
            'usepass'  => '0',
        ];

        $config          = new App();
        $config->baseURL = 'http://example.com/';

        $request = new IncomingRequest($config, new URI(), $rawstring, new UserAgent());

        $this->assertSame($expected, $request->getRawInput());
    }

    //--------------------------------------------------------------------

    public function testIsCLI()
    {
        // this should be the case in unit testing
        $this->assertTrue($this->request->isCLI());
    }

    public function testIsAJAX()
    {
        $this->request->appendHeader('X-Requested-With', 'XMLHttpRequest');
        $this->assertTrue($this->request->isAJAX());
    }

    //--------------------------------------------------------------------

    public function testIsSecure()
    {
        $_SERVER['HTTPS'] = 'on';
        $this->assertTrue($this->request->isSecure());
    }

    public function testIsSecureFrontEnd()
    {
        $this->request->appendHeader('Front-End-Https', 'on');
        $this->assertTrue($this->request->isSecure());
    }

    public function testIsSecureForwarded()
    {
        $this->request->appendHeader('X-Forwarded-Proto', 'https');
        $this->assertTrue($this->request->isSecure());
    }

    //--------------------------------------------------------------------

    public function testUserAgent()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla';

        $config  = new App();
        $request = new IncomingRequest($config, new URI(), null, new UserAgent());

        $this->assertSame('Mozilla', $request->getUserAgent()->__toString());
    }

    //--------------------------------------------------------------------

    public function testFileCollectionFactory()
    {
        $_FILES = [
            'userfile' => [
                'name'     => 'someFile.txt',
                'type'     => 'text/plain',
                'size'     => '124',
                'tmp_name' => '/tmp/myTempFile.txt',
                'error'    => 0,
            ],
        ];

        $files = $this->request->getFiles();
        $this->assertCount(1, $files);

        $file = array_shift($files);
        $this->assertInstanceOf(UploadedFile::class, $file);

        $this->assertSame('someFile.txt', $file->getName());
        $this->assertSame(124, $file->getSize());
    }

    //--------------------------------------------------------------------

    public function testGetFileMultiple()
    {
        $_FILES = [
            'userfile' => [
                'name' => [
                    'someFile.txt',
                    'someFile2.txt',
                ],
                'type' => [
                    'text/plain',
                    'text/plain',
                ],
                'size' => [
                    '124',
                    '125',
                ],
                'tmp_name' => [
                    '/tmp/myTempFile.txt',
                    '/tmp/myTempFile2.txt',
                ],
                'error' => [
                    0,
                    0,
                ],
            ],
        ];

        $gotit = $this->request->getFileMultiple('userfile');
        $this->assertSame(124, $gotit[0]->getSize());
        $this->assertSame(125, $gotit[1]->getSize());
    }

    //--------------------------------------------------------------------

    public function testGetFile()
    {
        $_FILES = [
            'userfile' => [
                'name'     => 'someFile.txt',
                'type'     => 'text/plain',
                'size'     => '124',
                'tmp_name' => '/tmp/myTempFile.txt',
                'error'    => 0,
            ],
        ];

        $gotit = $this->request->getFile('userfile');
        $this->assertSame(124, $gotit->getSize());
    }

    //--------------------------------------------------------------------

    public function testSpoofing()
    {
        $this->request->setMethod('WINK');
        $this->assertSame('wink', $this->request->getMethod());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/2839
     */
    public function testGetPostEmpty()
    {
        $_POST['TEST'] = '5';
        $_GET['TEST']  = '3';
        $this->assertSame($_POST, $this->request->getPostGet());
        $this->assertSame($_GET, $this->request->getGetPost());
    }

    public function testWithFalseBody()
    {
        // Use `false` here to simulate file_get_contents returning a false value
        $request = new IncomingRequest(new App(), new URI(), false, new UserAgent());

        $this->assertTrue($request->getBody() !== false);
        $this->assertTrue($request->getBody() === null);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/3020
     */
    public function testGetPostIndexNotExists()
    {
        $_POST['TEST'] = 5;
        $_GET['TEST']  = 3;
        $this->assertNull($this->request->getPostGet('gc'));
        $this->assertNull($this->request->getGetPost('gc'));
    }

    public function providePathChecks()
    {
        return [
            'not /index.php' => [
                '/test.php',
                '/',
            ],
            '/index.php' => [
                '/index.php',
                '/',
            ],
        ];
    }

    /**
     * @dataProvider providePathChecks
     */
    public function testExtensionPHP($path, $detectPath)
    {
        $config          = new App();
        $config->baseURL = 'http://example.com/';

        $_SERVER['REQUEST_URI'] = $path;
        $_SERVER['SCRIPT_NAME'] = $path;
        $request                = new IncomingRequest($config, new URI($path), null, new UserAgent());
        $this->assertSame($detectPath, $request->detectPath());
    }

    //--------------------------------------------------------------------

    public function testGetPath()
    {
        $_SERVER['REQUEST_URI'] = '/index.php/fruits/banana';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $request = new IncomingRequest(new App(), new URI(), null, new UserAgent());

        $this->assertSame('fruits/banana', $request->getPath());
    }

    public function testGetPathIsRelative()
    {
        $_SERVER['REQUEST_URI'] = '/sub/folder/index.php/fruits/banana';
        $_SERVER['SCRIPT_NAME'] = '/sub/folder/index.php';

        $request = new IncomingRequest(new App(), new URI(), null, new UserAgent());

        $this->assertSame('fruits/banana', $request->getPath());
    }

    public function testGetPathStoresDetectedValue()
    {
        $_SERVER['REQUEST_URI'] = '/fruits/banana';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $request = new IncomingRequest(new App(), new URI(), null, new UserAgent());

        $_SERVER['REQUEST_URI'] = '/candy/snickers';

        $this->assertSame('fruits/banana', $request->getPath());
    }

    public function testGetPathIsRediscovered()
    {
        $_SERVER['REQUEST_URI'] = '/fruits/banana';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $request = new IncomingRequest(new App(), new URI(), null, new UserAgent());

        $_SERVER['REQUEST_URI'] = '/candy/snickers';
        $request->detectPath();

        $this->assertSame('candy/snickers', $request->getPath());
    }

    //--------------------------------------------------------------------

    public function testSetPath()
    {
        $request = new IncomingRequest(new App(), new URI(), null, new UserAgent());
        $this->assertSame('', $request->getPath());

        $request->setPath('foobar');
        $this->assertSame('foobar', $request->getPath());
    }

    public function testSetPathUpdatesURI()
    {
        $request = new IncomingRequest(new App(), new URI(), null, new UserAgent());

        $request->setPath('apples');

        $this->assertSame('apples', $request->uri->getPath());
    }
}
