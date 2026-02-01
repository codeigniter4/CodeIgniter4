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

namespace CodeIgniter;

use CodeIgniter\Exceptions\InvalidArgumentException;
use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[BackupGlobals(true)]
#[Group('Others')]
final class SuperglobalsTest extends CIUnitTestCase
{
    private Superglobals $superglobals;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superglobals = new Superglobals([], [], [], [], [], []);
    }

    // $_SERVER tests
    public function testServerGetSet(): void
    {
        $this->superglobals->setServer('TEST_KEY', 'test_value');

        $this->assertSame('test_value', $this->superglobals->server('TEST_KEY'));
        $this->assertSame('test_value', $_SERVER['TEST_KEY']);
    }

    public function testServerGetReturnsNullForNonExistent(): void
    {
        $this->assertNull($this->superglobals->server('NON_EXISTENT_KEY'));
    }

    public function testServerSetWithArray(): void
    {
        $this->superglobals->setServer('argv', ['arg1', 'arg2']);

        $this->assertSame(['arg1', 'arg2'], $this->superglobals->server('argv'));
    }

    public function testServerSetWithInt(): void
    {
        $this->superglobals->setServer('REQUEST_TIME', 1234567890);

        $this->assertSame(1234567890, $this->superglobals->server('REQUEST_TIME'));
    }

    public function testServerSetWithFloat(): void
    {
        $this->superglobals->setServer('REQUEST_TIME_FLOAT', 1234567890.123);

        $this->assertEqualsWithDelta(1234567890.123, $this->superglobals->server('REQUEST_TIME_FLOAT'), PHP_FLOAT_EPSILON);
    }

    public function testServerUnset(): void
    {
        $this->superglobals->setServer('TEST_KEY', 'value');
        $this->superglobals->unsetServer('TEST_KEY');

        $this->assertNull($this->superglobals->server('TEST_KEY'));
        $this->assertArrayNotHasKey('TEST_KEY', $_SERVER);
    }

    public function testServerGetArray(): void
    {
        $this->superglobals->setServer('KEY1', 'value1');
        $this->superglobals->setServer('KEY2', 'value2');

        $array = $this->superglobals->getServerArray();

        $this->assertArrayHasKey('KEY1', $array);
        $this->assertArrayHasKey('KEY2', $array);
        $this->assertSame('value1', $array['KEY1']);
        $this->assertSame('value2', $array['KEY2']);
    }

    public function testServerSetArray(): void
    {
        $data = ['KEY1' => 'value1', 'KEY2' => 'value2'];

        $this->superglobals->setServerArray($data);

        $this->assertSame('value1', $this->superglobals->server('KEY1'));
        $this->assertSame('value2', $this->superglobals->server('KEY2'));
        $this->assertSame($data, $_SERVER);
    }

    // $_GET tests
    public function testGetGetSet(): void
    {
        $this->superglobals->setGet('test', 'value1');

        $this->assertSame('value1', $this->superglobals->get('test'));
        $this->assertSame('value1', $_GET['test']);
    }

    public function testGetReturnsNullForNonExistent(): void
    {
        $this->assertNull($this->superglobals->get('non_existent'));
    }

    public function testGetSetWithArray(): void
    {
        $this->superglobals->setGet('colors', ['red', 'blue']);

        $this->assertSame(['red', 'blue'], $this->superglobals->get('colors'));
    }

    public function testGetUnset(): void
    {
        $this->superglobals->setGet('test', 'value');
        $this->superglobals->unsetGet('test');

        $this->assertNull($this->superglobals->get('test'));
        $this->assertArrayNotHasKey('test', $_GET);
    }

    public function testGetGetArray(): void
    {
        $this->superglobals->setGet('key1', 'value1');
        $this->superglobals->setGet('key2', 'value2');

        $array = $this->superglobals->getGetArray();

        $this->assertSame(['key1' => 'value1', 'key2' => 'value2'], $array);
    }

    public function testGetSetArray(): void
    {
        $data = ['key1' => 'value1', 'key2' => 'value2'];

        $this->superglobals->setGetArray($data);

        $this->assertSame('value1', $this->superglobals->get('key1'));
        $this->assertSame($data, $_GET);
    }

    // $_POST tests
    public function testPostGetSet(): void
    {
        $this->superglobals->setPost('test', 'value1');

        $this->assertSame('value1', $this->superglobals->post('test'));
        $this->assertSame('value1', $_POST['test']);
    }

    public function testPostReturnsNullForNonExistent(): void
    {
        $this->assertNull($this->superglobals->post('non_existent'));
    }

    public function testPostSetWithArray(): void
    {
        $this->superglobals->setPost('user', ['name' => 'John', 'age' => '30']);

        $this->assertSame(['name' => 'John', 'age' => '30'], $this->superglobals->post('user'));
    }

    public function testPostUnset(): void
    {
        $this->superglobals->setPost('test', 'value');
        $this->superglobals->unsetPost('test');

        $this->assertNull($this->superglobals->post('test'));
        $this->assertArrayNotHasKey('test', $_POST);
    }

    public function testPostGetArray(): void
    {
        $this->superglobals->setPost('key1', 'value1');
        $this->superglobals->setPost('key2', 'value2');

        $array = $this->superglobals->getPostArray();

        $this->assertSame(['key1' => 'value1', 'key2' => 'value2'], $array);
    }

    public function testPostSetArray(): void
    {
        $data = ['key1' => 'value1', 'key2' => 'value2'];

        $this->superglobals->setPostArray($data);

        $this->assertSame('value1', $this->superglobals->post('key1'));
        $this->assertSame($data, $_POST);
    }

    // $_COOKIE tests
    public function testCookieGetSet(): void
    {
        $this->superglobals->setCookie('session', 'abc123');

        $this->assertSame('abc123', $this->superglobals->cookie('session'));
        $this->assertSame('abc123', $_COOKIE['session']);
    }

    public function testCookieReturnsNullForNonExistent(): void
    {
        $this->assertNull($this->superglobals->cookie('non_existent'));
    }

    public function testCookieSetWithArray(): void
    {
        $this->superglobals->setCookie('data', ['key' => 'value']);

        $this->assertSame(['key' => 'value'], $this->superglobals->cookie('data'));
    }

    public function testCookieUnset(): void
    {
        $this->superglobals->setCookie('test', 'value');
        $this->superglobals->unsetCookie('test');

        $this->assertNull($this->superglobals->cookie('test'));
        $this->assertArrayNotHasKey('test', $_COOKIE);
    }

    public function testCookieGetArray(): void
    {
        $this->superglobals->setCookie('key1', 'value1');
        $this->superglobals->setCookie('key2', 'value2');

        $array = $this->superglobals->getCookieArray();

        $this->assertSame(['key1' => 'value1', 'key2' => 'value2'], $array);
    }

    public function testCookieSetArray(): void
    {
        $data = ['key1' => 'value1', 'key2' => 'value2'];

        $this->superglobals->setCookieArray($data);

        $this->assertSame('value1', $this->superglobals->cookie('key1'));
        $this->assertSame($data, $_COOKIE);
    }

    // $_REQUEST tests
    public function testRequestGetSet(): void
    {
        $this->superglobals->setRequest('test', 'value1');

        $this->assertSame('value1', $this->superglobals->request('test'));
        $this->assertSame('value1', $_REQUEST['test']);
    }

    public function testRequestReturnsNullForNonExistent(): void
    {
        $this->assertNull($this->superglobals->request('non_existent'));
    }

    public function testRequestSetWithArray(): void
    {
        $this->superglobals->setRequest('data', ['key' => 'value']);

        $this->assertSame(['key' => 'value'], $this->superglobals->request('data'));
    }

    public function testRequestUnset(): void
    {
        $this->superglobals->setRequest('test', 'value');
        $this->superglobals->unsetRequest('test');

        $this->assertNull($this->superglobals->request('test'));
        $this->assertArrayNotHasKey('test', $_REQUEST);
    }

    public function testRequestGetArray(): void
    {
        $this->superglobals->setRequest('key1', 'value1');
        $this->superglobals->setRequest('key2', 'value2');

        $array = $this->superglobals->getRequestArray();

        $this->assertSame(['key1' => 'value1', 'key2' => 'value2'], $array);
    }

    public function testRequestSetArray(): void
    {
        $data = ['key1' => 'value1', 'key2' => 'value2'];

        $this->superglobals->setRequestArray($data);

        $this->assertSame('value1', $this->superglobals->request('key1'));
        $this->assertSame($data, $_REQUEST);
    }

    // $_FILES tests
    public function testFilesGetArray(): void
    {
        $filesData = [
            'upload' => [
                'name'     => 'document.pdf',
                'type'     => 'application/pdf',
                'tmp_name' => '/tmp/phpTest',
                'error'    => UPLOAD_ERR_OK,
                'size'     => 12345,
            ],
        ];

        $this->superglobals->setFilesArray($filesData);

        $this->assertSame($filesData, $this->superglobals->getFilesArray());
        $this->assertSame($filesData, $_FILES);
    }

    public function testFilesSetArrayWithMultipleFiles(): void
    {
        $filesData = [
            'photos' => [
                'name'     => ['photo1.jpg', 'photo2.jpg'],
                'type'     => ['image/jpeg', 'image/jpeg'],
                'tmp_name' => ['/tmp/phpA', '/tmp/phpB'],
                'error'    => [UPLOAD_ERR_OK, UPLOAD_ERR_OK],
                'size'     => [1234, 5678],
            ],
        ];

        $this->superglobals->setFilesArray($filesData);

        $this->assertSame($filesData, $this->superglobals->getFilesArray());
        $this->assertSame($filesData, $_FILES);
    }

    public function testFilesSetArrayEmpty(): void
    {
        $this->superglobals->setFilesArray([
            'upload' => [
                'name'     => 'test.txt',
                'type'     => 'text/plain',
                'tmp_name' => '/tmp/test',
                'error'    => UPLOAD_ERR_OK,
                'size'     => 100,
            ],
        ]);

        // Reset to empty
        $this->superglobals->setFilesArray([]);

        $this->assertSame([], $this->superglobals->getFilesArray());
        $this->assertSame([], $_FILES);
    }

    // Generic methods
    public function testGetGlobalArray(): void
    {
        $this->superglobals->setGet('test', 'value');

        $this->assertSame(['test' => 'value'], $this->superglobals->getGlobalArray('get'));
    }

    public function testGetGlobalArrayForFiles(): void
    {
        $filesData = [
            'upload' => [
                'name'     => 'test.pdf',
                'type'     => 'application/pdf',
                'tmp_name' => '/tmp/phpTest',
                'error'    => UPLOAD_ERR_OK,
                'size'     => 999,
            ],
        ];

        $this->superglobals->setFilesArray($filesData);

        $this->assertSame($filesData, $this->superglobals->getGlobalArray('files'));
    }

    public function testGetGlobalArrayThrowsExceptionForInvalidName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid superglobal name 'invalid'. Must be one of: server, get, post, cookie, files, request.");

        $this->superglobals->getGlobalArray('invalid');
    }

    public function testSetGlobalArray(): void
    {
        $data = ['key' => 'value'];

        $this->superglobals->setGlobalArray('post', $data);

        $this->assertSame('value', $this->superglobals->post('key'));
        $this->assertSame($data, $_POST);
    }

    public function testSetGlobalArrayForFiles(): void
    {
        $filesData = [
            'doc' => [
                'name'     => 'file.txt',
                'type'     => 'text/plain',
                'tmp_name' => '/tmp/test',
                'error'    => UPLOAD_ERR_OK,
                'size'     => 555,
            ],
        ];

        $this->superglobals->setGlobalArray('files', $filesData);

        $this->assertSame($filesData, $this->superglobals->getFilesArray());
        $this->assertSame($filesData, $_FILES);
    }

    public function testSetGlobalArrayThrowsExceptionForInvalidName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid superglobal name 'invalid'. Must be one of: server, get, post, cookie, files, request.");

        $this->superglobals->setGlobalArray('invalid', ['key' => 'value']);
    }

    // Constructor tests
    public function testConstructorWithCustomArrays(): void
    {
        $server  = ['SERVER_KEY' => 'server_value'];
        $get     = ['get_key' => 'get_value'];
        $post    = ['post_key' => 'post_value'];
        $cookie  = ['cookie_key' => 'cookie_value'];
        $request = ['request_key' => 'request_value'];
        $files   = [
            'upload' => [
                'name'     => 'custom.pdf',
                'type'     => 'application/pdf',
                'tmp_name' => '/tmp/custom',
                'error'    => UPLOAD_ERR_OK,
                'size'     => 7777,
            ],
        ];

        $superglobals = new Superglobals($server, $get, $post, $cookie, $files, $request);

        $this->assertSame('server_value', $superglobals->server('SERVER_KEY'));
        $this->assertSame('get_value', $superglobals->get('get_key'));
        $this->assertSame('post_value', $superglobals->post('post_key'));
        $this->assertSame('cookie_value', $superglobals->cookie('cookie_key'));
        $this->assertSame('request_value', $superglobals->request('request_key'));
        $this->assertSame($files, $superglobals->getFilesArray());
    }

    public function testConstructorSynchronizesWithPhpSuperglobals(): void
    {
        $server  = ['CUSTOM_SERVER' => 'server_val'];
        $get     = ['custom_get' => 'get_val'];
        $post    = ['custom_post' => 'post_val'];
        $cookie  = ['custom_cookie' => 'cookie_val'];
        $request = ['custom_request' => 'request_val'];
        $files   = [
            'doc' => [
                'name'     => 'test.pdf',
                'type'     => 'application/pdf',
                'tmp_name' => '/tmp/test',
                'error'    => UPLOAD_ERR_OK,
                'size'     => 999,
            ],
        ];

        new Superglobals($server, $get, $post, $cookie, $files, $request);

        // Verify PHP superglobals are synchronized
        $this->assertSame('server_val', $_SERVER['CUSTOM_SERVER']);
        $this->assertSame('get_val', $_GET['custom_get']);
        $this->assertSame('post_val', $_POST['custom_post']);
        $this->assertSame('cookie_val', $_COOKIE['custom_cookie']);
        $this->assertSame('request_val', $_REQUEST['custom_request']);
        $this->assertSame($files, $_FILES);
    }

    // Fluent API tests
    public function testFluentApiMethodChaining(): void
    {
        $result = $this->superglobals
            ->setServer('KEY1', 'value1')
            ->setGet('KEY2', 'value2')
            ->setPost('KEY3', 'value3')
            ->setCookie('KEY4', 'value4')
            ->setRequest('KEY5', 'value5');

        $this->assertInstanceOf(Superglobals::class, $result);
        $this->assertSame('value1', $this->superglobals->server('KEY1'));
        $this->assertSame('value2', $this->superglobals->get('KEY2'));
        $this->assertSame('value3', $this->superglobals->post('KEY3'));
        $this->assertSame('value4', $this->superglobals->cookie('KEY4'));
        $this->assertSame('value5', $this->superglobals->request('KEY5'));
    }

    public function testFluentApiWithUnset(): void
    {
        $result = $this->superglobals
            ->setServer('KEY1', 'value1')
            ->setServer('KEY2', 'value2')
            ->setGet('KEY3', 'value3')
            ->unsetServer('KEY1')
            ->unsetGet('KEY3');

        $this->assertInstanceOf(Superglobals::class, $result);
        $this->assertNull($this->superglobals->server('KEY1'));
        $this->assertSame('value2', $this->superglobals->server('KEY2'));
        $this->assertNull($this->superglobals->get('KEY3'));
    }

    public function testFluentApiWithArraySetters(): void
    {
        $serverData = ['SERVER1' => 'val1', 'SERVER2' => 'val2'];
        $getData    = ['get1' => 'val3'];

        $result = $this->superglobals
            ->setServerArray($serverData)
            ->setGetArray($getData)
            ->setPostArray(['post1' => 'val4']);

        $this->assertInstanceOf(Superglobals::class, $result);
        $this->assertSame('val1', $this->superglobals->server('SERVER1'));
        $this->assertSame('val2', $this->superglobals->server('SERVER2'));
        $this->assertSame('val3', $this->superglobals->get('get1'));
        $this->assertSame('val4', $this->superglobals->post('post1'));
    }

    public function testFluentApiMixedOperations(): void
    {
        $result = $this->superglobals
            ->setServerArray(['KEY1' => 'value1'])
            ->setServer('KEY2', 'value2')
            ->unsetServer('KEY1')
            ->setGet('test', 'data');

        $this->assertInstanceOf(Superglobals::class, $result);
        $this->assertNull($this->superglobals->server('KEY1'));
        $this->assertSame('value2', $this->superglobals->server('KEY2'));
        $this->assertSame('data', $this->superglobals->get('test'));
    }
}
