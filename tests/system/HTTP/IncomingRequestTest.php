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

use CodeIgniter\Config\Factories;
use CodeIgniter\Exceptions\ConfigException;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;
use InvalidArgumentException;
use TypeError;

/**
 * @backupGlobals enabled
 *
 * @internal
 *
 * @group SeparateProcess
 */
final class IncomingRequestTest extends CIUnitTestCase
{
    private Request $request;

    protected function setUp(): void
    {
        parent::setUp();

        $config        = new App();
        $this->request = $this->createRequest($config);

        $_POST = $_GET = $_SERVER = $_REQUEST = $_ENV = $_COOKIE = $_SESSION = [];
    }

    private function createRequest(?App $config = null, $body = null, ?string $path = null): IncomingRequest
    {
        $config ??= new App();
        $path ??= '';

        $uri = new SiteURI($config, $path);

        return new IncomingRequest($config, $uri, $body, new UserAgent());
    }

    public function testCanGrabRequestVars(): void
    {
        $_REQUEST['TEST'] = 5;

        $this->assertSame('5', $this->request->getVar('TEST'));
        $this->assertNull($this->request->getVar('TESTY'));
    }

    public function testCanGrabGetVars(): void
    {
        $_GET['TEST'] = 5;

        $this->assertSame('5', $this->request->getGet('TEST'));
        $this->assertNull($this->request->getGet('TESTY'));
    }

    public function testCanGrabPostVars(): void
    {
        $_POST['TEST'] = 5;

        $this->assertSame('5', $this->request->getPost('TEST'));
        $this->assertNull($this->request->getPost('TESTY'));
    }

    public function testCanGrabPostBeforeGet(): void
    {
        $_POST['TEST'] = 5;
        $_GET['TEST']  = 3;

        $this->assertSame('5', $this->request->getPostGet('TEST'));
        $this->assertSame('3', $this->request->getGetPost('TEST'));
    }

    public function testNoOldInput(): void
    {
        $this->assertNull($this->request->getOldInput('name'));
    }

    public function testCanGetOldInput(): void
    {
        $_SESSION['_ci_old_input'] = [
            'get'  => ['one' => 'two'],
            'post' => ['name' => 'foo'],
        ];

        $this->assertSame('foo', $this->request->getOldInput('name'));
        $this->assertSame('two', $this->request->getOldInput('one'));
    }

    public function testCanGetOldInputDotted(): void
    {
        $_SESSION['_ci_old_input'] = [
            'get'  => ['apple' => ['name' => 'two']],
            'post' => ['banana' => ['name' => 'foo']],
        ];

        $this->assertSame('foo', $this->request->getOldInput('banana.name'));
        $this->assertSame('two', $this->request->getOldInput('apple.name'));
    }

    public function testMissingOldInput(): void
    {
        $_SESSION['_ci_old_input'] = [
            'get'  => ['apple' => ['name' => 'two']],
            'post' => ['banana' => ['name' => 'foo']],
        ];

        $this->assertNull($this->request->getOldInput('pineapple.name'));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1492
     */
    public function testCanGetOldInputArrayWithSESSION(): void
    {
        $_SESSION['_ci_old_input'] = [
            'get'  => ['apple' => ['name' => 'two']],
            'post' => ['banana' => ['name' => 'foo']],
        ];

        $this->assertSame(['name' => 'two'], $this->request->getOldInput('apple'));
        $this->assertSame(['name' => 'foo'], $this->request->getOldInput('banana'));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1492
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testCanGetOldInputArrayWithSessionService(): void
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

    public function testCanGrabServerVars(): void
    {
        $server                   = $this->getPrivateProperty($this->request, 'globals');
        $server['server']['TEST'] = 5;
        $this->setPrivateProperty($this->request, 'globals', $server);

        $this->assertSame('5', $this->request->getServer('TEST'));
        $this->assertNull($this->request->getServer('TESTY'));
    }

    public function testCanGrabEnvVars(): void
    {
        $server                = $this->getPrivateProperty($this->request, 'globals');
        $server['env']['TEST'] = 5;
        $this->setPrivateProperty($this->request, 'globals', $server);

        $this->assertSame('5', $this->request->getEnv('TEST'));
        $this->assertNull($this->request->getEnv('TESTY'));
    }

    public function testCanGrabCookieVars(): void
    {
        $_COOKIE['TEST'] = 5;

        $this->assertSame('5', $this->request->getCookie('TEST'));
        $this->assertNull($this->request->getCookie('TESTY'));
    }

    public function testStoresDefaultLocale(): void
    {
        $config = new App();

        $this->assertSame($config->defaultLocale, $this->request->getDefaultLocale());
        $this->assertSame($config->defaultLocale, $this->request->getLocale());
    }

    public function testSetLocaleSaves(): void
    {
        $config                   = new App();
        $config->supportedLocales = ['en', 'es'];
        $config->defaultLocale    = 'es';
        $config->baseURL          = 'http://example.com/';

        $request = $this->createRequest($config);

        $request->setLocale('en');
        $this->assertSame('en', $request->getLocale());
    }

    public function testSetBadLocale(): void
    {
        $config                   = new App();
        $config->supportedLocales = ['en', 'es'];
        $config->defaultLocale    = 'es';
        $config->baseURL          = 'http://example.com/';

        $request = $this->createRequest($config);

        $request->setLocale('xx');
        $this->assertSame('es', $request->getLocale());
    }

    public function testSetValidLocales()
    {
        $config                   = new App();
        $config->supportedLocales = ['en', 'es'];
        $config->defaultLocale    = 'es';
        $config->baseURL          = 'http://example.com/';

        $request = new IncomingRequest($config, new URI(), null, new UserAgent());

        $request->setValidLocales(['ja']);
        $request->setLocale('ja');

        $this->assertSame('ja', $request->getLocale());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/2774
     */
    public function testNegotiatesLocale(): void
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'fr-FR; q=1.0, en; q=0.5';

        $config                   = new App();
        $config->negotiateLocale  = true;
        $config->supportedLocales = ['fr', 'en'];
        $config->baseURL          = 'http://example.com/';

        $request = $this->createRequest($config);

        $this->assertSame($config->defaultLocale, $request->getDefaultLocale());
        $this->assertSame('fr', $request->getLocale());
    }

    public function testNegotiatesLocaleOnlyBroad(): void
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'fr; q=1.0, en; q=0.5';

        $config                   = new App();
        $config->negotiateLocale  = true;
        $config->supportedLocales = ['fr', 'en'];
        $config->baseURL          = 'http://example.com/';

        $request = $this->createRequest($config);

        $this->assertSame($config->defaultLocale, $request->getDefaultLocale());
        $this->assertSame('fr', $request->getLocale());
    }

    // The negotiation tests below are not intended to exercise the HTTP\Negotiate class -
    // that is up to the NegotiateTest. These are only to make sure that the requests
    // flow through to the negotiator

    public function testNegotiatesNot(): void
    {
        $this->request->setHeader('Accept-Charset', 'iso-8859-5, unicode-1-1;q=0.8');

        $this->expectException(HTTPException::class);
        $this->request->negotiate('something bogus', ['iso-8859-5', 'unicode-1-1']);
    }

    public function testNegotiatesCharset(): void
    {
        // $_SERVER['HTTP_ACCEPT_CHARSET'] = 'iso-8859-5, unicode-1-1;q=0.8';
        $this->request->setHeader('Accept-Charset', 'iso-8859-5, unicode-1-1;q=0.8');

        $this->assertSame(
            'utf-8',
            $this->request->negotiate('charset', ['iso-8859', 'unicode-1-2'])
        );
    }

    public function testNegotiatesMedia(): void
    {
        $this->request->setHeader('Accept', 'text/plain; q=0.5, text/html, text/x-dvi; q=0.8, text/x-c');
        $this->assertSame(
            'text/html',
            $this->request->negotiate('media', ['text/html', 'text/x-c', 'text/x-dvi', 'text/plain'])
        );
    }

    public function testNegotiatesEncoding(): void
    {
        $this->request->setHeader('Accept-Encoding', 'gzip;q=1.0, identity; q=0.4, compress;q=0.5');
        $this->assertSame('gzip', $this->request->negotiate('encoding', ['gzip', 'compress']));
    }

    public function testNegotiatesLanguage(): void
    {
        $this->request->setHeader('Accept-Language', 'da, en-gb;q=0.8, en;q=0.7');
        $this->assertSame('en', $this->request->negotiate('language', ['en', 'da']));
    }

    public function testCanGrabGetRawJSON(): void
    {
        $json = '{"code":1, "message":"ok"}';

        $expected = ['code' => 1, 'message' => 'ok'];

        $config          = new App();
        $config->baseURL = 'http://example.com/';

        $request = $this->createRequest($config, $json);

        $this->assertSame($expected, $request->getJSON(true));
    }

    public function testCanGetAVariableFromJson(): void
    {
        $jsonObj = [
            'foo'   => 'bar',
            'baz'   => ['fizz' => 'buzz'],
            'int'   => 123,
            'float' => 3.14,
            'true'  => true,
            'false' => false,
            'null'  => null,
        ];
        $json = json_encode($jsonObj);

        $config          = new App();
        $config->baseURL = 'http://example.com/';

        $request = $this->createRequest($config, $json);

        $this->assertSame('bar', $request->getJsonVar('foo'));
        $this->assertNull($request->getJsonVar('notExists'));

        $jsonVar = $request->getJsonVar('baz');
        $this->assertIsObject($jsonVar);
        $this->assertSame('buzz', $jsonVar->fizz);
        $this->assertSame('buzz', $request->getJsonVar('baz.fizz'));
        $this->assertSame(123, $request->getJsonVar('int'));
        $this->assertSame(3.14, $request->getJsonVar('float'));
        $this->assertTrue($request->getJsonVar('true'));
        $this->assertFalse($request->getJsonVar('false'));
        $this->assertNull($request->getJsonVar('null'));
    }

    public function testGetJsonVarAsArray(): void
    {
        $jsonObj = [
            'baz' => [
                'fizz'  => 'buzz',
                'foo'   => 'bar',
                'int'   => 123,
                'float' => 3.14,
                'true'  => true,
                'false' => false,
                'null'  => null,
            ],
        ];
        $json = json_encode($jsonObj);

        $config          = new App();
        $config->baseURL = 'http://example.com/';

        $request = $this->createRequest($config, $json);

        $jsonVar = $request->getJsonVar('baz', true);
        $this->assertIsArray($jsonVar);
        $this->assertSame('buzz', $jsonVar['fizz']);
        $this->assertSame('bar', $jsonVar['foo']);
        $this->assertSame(123, $jsonVar['int']);
        $this->assertSame(3.14, $jsonVar['float']);
        $this->assertTrue($jsonVar['true']);
        $this->assertFalse($jsonVar['false']);
        $this->assertNull($jsonVar['null']);
    }

    public function testGetJsonVarCanFilter(): void
    {
        $json = json_encode(['foo' => 'bar']);

        $config          = new App();
        $config->baseURL = 'http://example.com/';

        $request = $this->createRequest($config, $json);

        $this->assertFalse($request->getJsonVar('foo', false, FILTER_VALIDATE_INT));
    }

    public function testGetJsonVarCanFilterArray(): void
    {
        $json = json_encode([
            'string'      => 'hello123world',
            'int'         => 123,
            'float'       => 3.14,
            'stringFloat' => 'hello3.14world',
            'array'       => [
                'string' => 'hello123world',
                'int'    => 123,
            ],
        ]);

        $config          = new App();
        $config->baseURL = 'http://example.com/';

        $request = $this->createRequest($config, $json);
        $request->setHeader('Content-Type', 'application/json');

        $expected = [
            'string'      => '123',
            'int'         => 123,
            'float'       => 3.14,
            'stringFloat' => '3.14',
            'array'       => [
                'string' => '123',
                'int'    => 123,
            ],
        ];

        $this->assertSame(
            $expected,
            $request->getJsonVar(null, true, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)
        );

        $this->assertSame(
            $expected['array'],
            $request->getJsonVar('array', true, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)
        );

        $this->assertSame(
            ['array' => $expected['array'], 'float' => $expected['float']],
            $request->getJsonVar(['array', 'float'], true, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)
        );

        $result = $request->getJsonVar(['array', 'float'], false, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $this->assertIsObject($result['array']);
        $this->assertSame(
            ['array' => $expected['array'], 'float' => $expected['float']],
            ['array' => json_decode(json_encode($result['array']), true), 'float' => $result['float']],
        );
    }

    public function testGetVarWorksWithJson(): void
    {
        $json = json_encode(['foo' => 'bar', 'fizz' => 'buzz']);

        $config          = new App();
        $config->baseURL = 'http://example.com/';

        $request = $this->createRequest($config, $json);
        $request->setHeader('Content-Type', 'application/json');

        $this->assertSame('bar', $request->getVar('foo'));
        $this->assertSame('buzz', $request->getVar('fizz'));
        $this->assertNull($request->getVar('notExists'));

        $multiple = $request->getVar(['foo', 'fizz']);
        $this->assertIsArray($multiple);
        $this->assertSame('bar', $multiple['foo']);
        $this->assertSame('buzz', $multiple['fizz']);

        $all = $request->getVar();
        $this->assertIsObject($all);
        $this->assertSame('bar', $all->foo);
        $this->assertSame('buzz', $all->fizz);
    }

    public function testGetVarWorksWithJsonAndGetParams(): void
    {
        $config          = new App();
        $config->baseURL = 'http://example.com/';

        // GET method
        $_REQUEST['foo']  = 'bar';
        $_REQUEST['fizz'] = 'buzz';

        $request = $this->createRequest($config, null);
        $request = $request->withMethod('GET');

        // JSON type
        $request->setHeader('Content-Type', 'application/json');

        // The body is null, so this works.
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

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/5391
     */
    public function testGetJsonVarReturnsNullFromNullBody(): void
    {
        $config          = new App();
        $config->baseURL = 'http://example.com/';
        $json            = null;
        $request         = $this->createRequest($config, $json);

        $this->assertNull($request->getJsonVar('myKey'));
    }

    public function testgetJSONReturnsNullFromNullBody(): void
    {
        $config          = new App();
        $config->baseURL = 'http://example.com/';
        $json            = null;
        $request         = $this->createRequest($config, $json);

        $this->assertNull($request->getJSON());
    }

    public function testCanGrabGetRawInput(): void
    {
        $rawstring = 'username=admin001&role=administrator&usepass=0';

        $expected = [
            'username' => 'admin001',
            'role'     => 'administrator',
            'usepass'  => '0',
        ];

        $config          = new App();
        $config->baseURL = 'http://example.com/';

        $request = $this->createRequest($config, $rawstring);

        $this->assertSame($expected, $request->getRawInput());
    }

    public static function provideCanGrabGetRawInputVar(): iterable
    {
        return [
            [
                'username=admin001&role=administrator&usepass=0',
                'username',
                'admin001',
                null,
                null,
            ],
            [
                'username=admin001&role=administrator&usepass=0',
                ['role', 'usepass'],
                [
                    'role'    => 'administrator',
                    'usepass' => '0',
                ],
                null,
                null,
            ],
            [
                'username=admin001&role=administrator&usepass=0',
                'not_exists',
                null,
                null,
                null,
            ],
            [
                'username=admin001&role=administrator&usepass=0',
                null,
                [
                    'username' => 'admin001',
                    'role'     => 'administrator',
                    'usepass'  => '0',
                ],
                null,
                null,
            ],
            [
                '',
                null,
                [],
                null,
                null,
            ],
            [
                'username=admin001&role=administrator&usepass=0&foo[]=one&foo[]=two',
                ['username', 'foo'],
                [
                    'username' => 'admin001',
                    'foo'      => ['one', 'two'],
                ],
                null,
                null,
            ],
            [
                'username=admin001&role=administrator&usepass=0&foo[]=one&foo[]=two',
                'foo.0',
                'one',
                null,
                null,
            ],
            [
                'username=admin001&role=administrator&usepass=0&foo[]=one&foo[]=two&bar[baz]=hello',
                'bar.baz',
                'hello',
                null,
                null,
            ],
            [
                'username=admin001&role=administrator&usepass=0&foo[]=one&foo[]=two&bar[baz]=hello6.5world',
                'bar.baz',
                '6.5',
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION,
            ],
        ];
    }

    /**
     * @dataProvider provideCanGrabGetRawInputVar
     *
     * @param string $rawstring
     * @param mixed  $var
     * @param mixed  $expected
     * @param mixed  $filter
     * @param mixed  $flag
     */
    public function testCanGrabGetRawInputVar($rawstring, $var, $expected, $filter, $flag): void
    {
        $config          = new App();
        $config->baseURL = 'http://example.com/';

        $request = $this->createRequest($config, $rawstring);

        $this->assertSame($expected, $request->getRawInputVar($var, $filter, $flag));
    }

    /**
     * @dataProvider provideIsHTTPMethods
     */
    public function testIsHTTPMethodLowerCase(string $value): void
    {
        $request = $this->request->withMethod($value);

        $this->assertTrue($request->is(strtolower($value)));
    }

    public static function provideIsHTTPMethods(): iterable
    {
        yield from [
            ['GET'],
            ['POST'],
            ['PUT'],
            ['DELETE'],
            ['HEAD'],
            ['PATCH'],
            ['OPTIONS'],
        ];
    }

    /**
     * @dataProvider provideIsHTTPMethods
     */
    public function testIsHTTPMethodUpperCase(string $value): void
    {
        $request = $this->request->withMethod($value);

        $this->assertTrue($request->is($value));
    }

    public function testIsInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown type: invalid');

        $request = $this->request->withMethod('GET');

        $request->is('invalid');
    }

    public function testIsJson(): void
    {
        $request = $this->request->setHeader('Content-Type', 'application/json');

        $this->assertTrue($request->is('json'));
    }

    public function testIsWithAjax(): void
    {
        $request = $this->request->setHeader('X-Requested-With', 'XMLHttpRequest');

        $this->assertTrue($request->is('ajax'));
    }

    public function testIsCLI(): void
    {
        $this->assertFalse($this->request->isCLI());
    }

    public function testIsAJAX(): void
    {
        $this->request->appendHeader('X-Requested-With', 'XMLHttpRequest');
        $this->assertTrue($this->request->isAJAX());
    }

    public function testIsSecure(): void
    {
        $_SERVER['HTTPS'] = 'on';
        $this->assertTrue($this->request->isSecure());
    }

    public function testIsSecureFrontEnd(): void
    {
        $this->request->appendHeader('Front-End-Https', 'on');
        $this->assertTrue($this->request->isSecure());
    }

    public function testIsSecureForwarded(): void
    {
        $this->request->appendHeader('X-Forwarded-Proto', 'https');
        $this->assertTrue($this->request->isSecure());
    }

    public function testUserAgent(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla';

        $config  = new App();
        $request = $this->createRequest($config);

        $this->assertSame('Mozilla', $request->getUserAgent()->__toString());
    }

    public function testFileCollectionFactory(): void
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

    public function testGetFileMultiple(): void
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

    public function testGetFile(): void
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

    public function testSpoofing(): void
    {
        $this->request->setMethod('WINK');
        $this->assertSame('wink', $this->request->getMethod());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/2839
     */
    public function testGetPostEmpty(): void
    {
        $_POST['TEST'] = '5';
        $_GET['TEST']  = '3';
        $this->assertSame($_POST, $this->request->getPostGet());
        $this->assertSame($_GET, $this->request->getGetPost());
    }

    public function testPostGetSecondStream(): void
    {
        $_GET['get'] = '3';
        $this->assertSame($_GET, $this->request->getPostGet());
    }

    public function testGetPostSecondStream(): void
    {
        $_POST['post'] = '5';
        $this->assertSame($_POST, $this->request->getGetPost());
    }

    public function testGetPostSecondStreams(): void
    {
        $_GET['get']   = '3';
        $_POST['post'] = '5';
        $this->assertSame(array_merge($_GET, $_POST), $this->request->getPostGet());
        $this->assertSame(array_merge($_POST, $_GET), $this->request->getGetPost());
    }

    public function testWithFalseBody(): void
    {
        // Use `false` here to simulate file_get_contents returning a false value
        $request = $this->createRequest(null, false);

        $this->assertNotFalse($request->getBody());
        $this->assertNull($request->getBody());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/3020
     */
    public function testGetPostIndexNotExists(): void
    {
        $_POST['TEST'] = 5;
        $_GET['TEST']  = 3;
        $this->assertNull($this->request->getPostGet('gc'));
        $this->assertNull($this->request->getGetPost('gc'));
    }

    public static function provideExtensionPHP(): iterable
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
     * @dataProvider provideExtensionPHP
     *
     * @param mixed $path
     * @param mixed $detectPath
     */
    public function testExtensionPHP($path, $detectPath): void
    {
        $config          = new App();
        $config->baseURL = 'http://example.com/';

        $_SERVER['REQUEST_URI'] = $path;
        $_SERVER['SCRIPT_NAME'] = $path;
        $request                = new IncomingRequest($config, new URI($path), null, new UserAgent());
        $this->assertSame($detectPath, $request->detectPath());
    }

    public function testGetPath(): void
    {
        $request = $this->createRequest(null, null, 'fruits/banana');

        $this->assertSame('fruits/banana', $request->getPath());
    }

    public function testSetPath(): void
    {
        $request = new IncomingRequest(new App(), new URI(), null, new UserAgent());
        $this->assertSame('', $request->getPath());

        $request->setPath('foobar');
        $this->assertSame('foobar', $request->getPath());
    }

    public function testGetIPAddressNormal(): void
    {
        $expected               = '123.123.123.123';
        $_SERVER['REMOTE_ADDR'] = $expected;

        $this->request = new Request(new App());
        $this->request->populateHeaders();

        $this->assertSame($expected, $this->request->getIPAddress());
        // call a second time to exercise the initial conditional block in getIPAddress()
        $this->assertSame($expected, $this->request->getIPAddress());
    }

    public function testGetIPAddressThruProxy(): void
    {
        $expected                        = '123.123.123.123';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = $expected;
        $_SERVER['REMOTE_ADDR']          = '10.0.1.200';

        $config           = new App();
        $config->proxyIPs = [
            '10.0.1.200'     => 'X-Forwarded-For',
            '192.168.5.0/24' => 'X-Forwarded-For',
        ];
        Factories::injectMock('config', App::class, $config);
        $this->request = new Request();
        $this->request->populateHeaders();

        // we should see the original forwarded address
        $this->assertSame($expected, $this->request->getIPAddress());
    }

    public function testGetIPAddressThruProxyIPv6(): void
    {
        $expected                        = '123.123.123.123';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = $expected;
        $_SERVER['REMOTE_ADDR']          = '2001:db8::2:1';

        $config           = new App();
        $config->proxyIPs = [
            '2001:db8::2:1' => 'X-Forwarded-For',
        ];
        Factories::injectMock('config', App::class, $config);
        $this->request = new Request();
        $this->request->populateHeaders();

        // we should see the original forwarded address
        $this->assertSame($expected, $this->request->getIPAddress());
    }

    public function testGetIPAddressThruProxyInvalidIPAddress(): void
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '123.456.23.123';
        $expected                        = '10.0.1.200';
        $_SERVER['REMOTE_ADDR']          = $expected;

        $config           = new App();
        $config->proxyIPs = [
            '10.0.1.200'     => 'X-Forwarded-For',
            '192.168.5.0/24' => 'X-Forwarded-For',
        ];
        $this->request = new Request($config);
        $this->request->populateHeaders();

        // spoofed address invalid
        $this->assertSame($expected, $this->request->getIPAddress());
    }

    public function testGetIPAddressThruProxyInvalidIPAddressIPv6(): void
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '2001:xyz::1';
        $expected                        = '2001:db8::2:1';
        $_SERVER['REMOTE_ADDR']          = $expected;

        $config           = new App();
        $config->proxyIPs = [
            '2001:db8::2:1' => 'X-Forwarded-For',
        ];
        $this->request = new Request($config);
        $this->request->populateHeaders();

        // spoofed address invalid
        $this->assertSame($expected, $this->request->getIPAddress());
    }

    public function testGetIPAddressThruProxyNotWhitelisted(): void
    {
        $expected                        = '10.10.1.200';
        $_SERVER['REMOTE_ADDR']          = $expected;
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '123.456.23.123';

        $config           = new App();
        $config->proxyIPs = [
            '10.0.1.200'     => 'X-Forwarded-For',
            '192.168.5.0/24' => 'X-Forwarded-For',
        ];
        $this->request = new Request($config);
        $this->request->populateHeaders();

        // spoofed address invalid
        $this->assertSame($expected, $this->request->getIPAddress());
    }

    public function testGetIPAddressThruProxyNotWhitelistedIPv6(): void
    {
        $expected                        = '2001:db8::2:2';
        $_SERVER['REMOTE_ADDR']          = $expected;
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '123.456.23.123';

        $config           = new App();
        $config->proxyIPs = [
            '2001:db8::2:1' => 'X-Forwarded-For',
        ];
        $this->request = new Request($config);
        $this->request->populateHeaders();

        // spoofed address invalid
        $this->assertSame($expected, $this->request->getIPAddress());
    }

    public function testGetIPAddressThruProxySubnet(): void
    {
        $expected                        = '123.123.123.123';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = $expected;
        $_SERVER['REMOTE_ADDR']          = '192.168.5.21';

        $config           = new App();
        $config->proxyIPs = ['192.168.5.0/24' => 'X-Forwarded-For'];
        Factories::injectMock('config', App::class, $config);
        $this->request = new Request();
        $this->request->populateHeaders();

        // we should see the original forwarded address
        $this->assertSame($expected, $this->request->getIPAddress());
    }

    public function testGetIPAddressThruProxySubnetIPv6(): void
    {
        $expected                        = '123.123.123.123';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = $expected;
        $_SERVER['REMOTE_ADDR']          = '2001:db8:1234:ffff:ffff:ffff:ffff:ffff';

        $config           = new App();
        $config->proxyIPs = ['2001:db8:1234::/48' => 'X-Forwarded-For'];
        Factories::injectMock('config', App::class, $config);
        $this->request = new Request();
        $this->request->populateHeaders();

        // we should see the original forwarded address
        $this->assertSame($expected, $this->request->getIPAddress());
    }

    public function testGetIPAddressThruProxyOutOfSubnet(): void
    {
        $expected                        = '192.168.5.21';
        $_SERVER['REMOTE_ADDR']          = $expected;
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '123.123.123.123';

        $config           = new App();
        $config->proxyIPs = ['192.168.5.0/28' => 'X-Forwarded-For'];
        $this->request    = new Request($config);
        $this->request->populateHeaders();

        // we should see the original forwarded address
        $this->assertSame($expected, $this->request->getIPAddress());
    }

    public function testGetIPAddressThruProxyOutOfSubnetIPv6(): void
    {
        $expected                        = '2001:db8:1235:ffff:ffff:ffff:ffff:ffff';
        $_SERVER['REMOTE_ADDR']          = $expected;
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '123.123.123.123';

        $config           = new App();
        $config->proxyIPs = ['2001:db8:1234::/48' => 'X-Forwarded-For'];
        $this->request    = new Request($config);
        $this->request->populateHeaders();

        // we should see the original forwarded address
        $this->assertSame($expected, $this->request->getIPAddress());
    }

    public function testGetIPAddressThruProxyBothIPv4AndIPv6(): void
    {
        $expected                        = '2001:db8:1235:ffff:ffff:ffff:ffff:ffff';
        $_SERVER['REMOTE_ADDR']          = $expected;
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '123.123.123.123';

        $config           = new App();
        $config->proxyIPs = [
            '192.168.5.0/28'     => 'X-Forwarded-For',
            '2001:db8:1234::/48' => 'X-Forwarded-For',
        ];
        $this->request = new Request($config);
        $this->request->populateHeaders();

        // we should see the original forwarded address
        $this->assertSame($expected, $this->request->getIPAddress());
    }

    public function testGetIPAddressThruProxyInvalidConfigString(): void
    {
        $this->expectException(TypeError::class);

        $config           = new App();
        $config->proxyIPs = '192.168.5.0/28';
        $this->request    = new Request($config);
        $this->request->populateHeaders();

        $this->request->getIPAddress();
    }

    public function testGetIPAddressThruProxyInvalidConfigArray(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage(
            'You must set an array with Proxy IP address key and HTTP header name value in Config\App::$proxyIPs.'
        );

        $config           = new App();
        $config->proxyIPs = ['192.168.5.0/28'];
        Factories::injectMock('config', App::class, $config);
        $this->request = new Request();
        $this->request->populateHeaders();

        $this->request->getIPAddress();
    }

    // @TODO getIPAddress should have more testing, to 100% code coverage
}
