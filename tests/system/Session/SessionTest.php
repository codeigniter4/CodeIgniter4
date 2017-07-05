<?php namespace CodeIgniter\Session;

use CodeIgniter\Log\TestLogger;
use CodeIgniter\Session\Handlers\FileHandler;
use Config\Logger;

class SessionTest extends \CIUnitTestCase
{
    public function setUp()
    {
        $_COOKIE = [];
        $_SESSION = [];
    }

    public function tearDown()
    {

    }

    protected function getInstance($options=[])
    {
        $defaults = [
            'sessionDriver' => 'CodeIgniter\Session\Handlers\FileHandler',
            'sessionCookieName' => 'ci_session',
            'sessionExpiration' => 7200,
            'sessionSavePath' => null,
            'sessionMatchIP' => false,
            'sessionTimeToUpdate' => 300,
            'sessionRegenerateDestroy' => false,
            'cookieDomain' => '',
            'cookiePrefix' => '',
            'cookiePath' => '/',
            'cookieSecure' => false,
        ];

        $config = array_merge($defaults, $options);
        $config = (object)$config;

        $session = new MockSession(new FileHandler($config), $config);
        $session->setLogger(new TestLogger(new Logger()));

        return $session;
    }

    public function testSessionSetsRegenerateTime()
    {
        $session = $this->getInstance();
        $session->start();

        $this->assertTrue(isset($_SESSION['__ci_last_regenerate']) && ! empty($_SESSION['__ci_last_regenerate']));
    }

    public function testWillRegenerateSessionAutomatically()
    {
        $session = $this->getInstance();

        $time = time()-400;
        $_SESSION['__ci_last_regenerate'] = $time;
        $session->start();

        $this->assertTrue($session->didRegenerate);
        $this->assertTrue($_SESSION['__ci_last_regenerate'] > $time+90);
    }

    public function testCanSetSingleValue()
    {
        $session = $this->getInstance();
        $session->start();

        $session->set('foo', 'bar');

        $this->assertEquals('bar', $_SESSION['foo']);
    }

    public function testCanSetArray()
    {
        $session = $this->getInstance();
        $session->start();

        $session->set([
            'foo' => 'bar',
            'bar' => 'baz'
        ]);

        $this->assertEquals('bar', $_SESSION['foo']);
        $this->assertEquals('baz', $_SESSION['bar']);
        $this->assertFalse(isset($_SESSION['__ci_vars']));
    }

    public function testGetSimpleKey()
    {
        $session = $this->getInstance();
        $session->start();

        $session->set('foo', 'bar');

        $this->assertEquals('bar', $session->get('foo'));
    }

    public function testGetReturnsNullWhenNotFound()
    {
        $session = $this->getInstance();
        $session->start();

        $this->assertNull($session->get('foo'));
    }

    public function testGetAsProperty()
    {
        $session = $this->getInstance();
        $session->start();

        $session->set('foo', 'bar');

        $this->assertEquals('bar', $session->foo);
    }

    public function testGetAsNormal()
    {
        $session = $this->getInstance();
        $session->start();

        $session->set('foo', 'bar');

        $this->assertEquals('bar', $_SESSION['foo']);
    }

    public function testHasReturnsTrueOnSuccess()
    {
        $session = $this->getInstance();
        $session->start();

        $_SESSION['foo'] = 'bar';

        $this->assertTrue($session->has('foo'));
    }

    public function testHasReturnsFalseOnNotFound()
    {
        $session = $this->getInstance();
        $session->start();

        $_SESSION['foo'] = 'bar';

        $this->assertFalse($session->has('bar'));
    }

    public function testRemoveActuallyRemoves()
    {
        $session = $this->getInstance();
        $session->start();

        $_SESSION['foo'] = 'bar';
        $session->remove('foo');

        $this->assertFalse(isset($_SESSION['foo']));
        $this->assertFalse($session->has('foo'));
    }

    public function testHasReturnsCanRemoveArray()
    {
        $session = $this->getInstance();
        $session->start();

        $_SESSION = [
            'foo' => 'bar',
            'bar' => 'baz'
        ];

        $this->assertTrue($session->has('foo'));

        $session->remove(['foo', 'bar']);

        $this->assertFalse(isset($_SESSION['foo']));
        $this->assertFalse(isset($_SESSION['bar']));
    }

    public function testSetMagicMethod()
    {
        $session = $this->getInstance();
        $session->start();

        $session->foo = 'bar';

        $this->assertTrue(isset($_SESSION['foo']));
        $this->assertEquals('bar', $_SESSION['foo']);
    }

    public function testCanFlashData()
    {
        $session = $this->getInstance();
        $session->start();

        $session->setFlashdata('foo', 'bar');

        $this->assertTrue($session->has('foo'));
        $this->assertEquals('new', $_SESSION['__ci_vars']['foo']);

        // Should reset the 'new' to 'old'
        $session->start();

        $this->assertTrue($session->has('foo'));
        $this->assertEquals('old', $_SESSION['__ci_vars']['foo']);

        // Should no longer be available
        $session->start();

        $this->assertFalse($session->has('foo'));
    }

    public function testCanFlashArray()
    {
        $session = $this->getInstance();
        $session->start();

        $session->setFlashdata([
            'foo' => 'bar',
            'bar' => 'baz'
        ]);

        $this->assertTrue($session->has('foo'));
        $this->assertEquals('new', $_SESSION['__ci_vars']['foo']);
        $this->assertTrue($session->has('bar'));
        $this->assertEquals('new', $_SESSION['__ci_vars']['bar']);
    }

    public function testKeepFlashData()
    {
        $session = $this->getInstance();
        $session->start();

        $session->setFlashdata('foo', 'bar');

        $this->assertTrue($session->has('foo'));
        $this->assertEquals('new', $_SESSION['__ci_vars']['foo']);

        // Should reset the 'new' to 'old'
        $session->start();

        $this->assertTrue($session->has('foo'));
        $this->assertEquals('old', $_SESSION['__ci_vars']['foo']);

        $session->keepFlashdata('foo');

        $this->assertEquals('new', $_SESSION['__ci_vars']['foo']);

        // Should no longer be available
        $session->start();

        $this->assertTrue($session->has('foo'));
        $this->assertEquals('old', $_SESSION['__ci_vars']['foo']);
    }

    public function testUnmarkFlashDataRemovesData()
    {
        $session = $this->getInstance();
        $session->start();

        $session->setFlashdata('foo', 'bar');
        $session->set('bar', 'baz');

        $this->assertTrue($session->has('foo'));
        $this->assertTrue(isset($_SESSION['__ci_vars']['foo']));

        $session->unmarkFlashdata('foo');

        // Should still be here
        $this->assertTrue($session->has('foo'));
        // but no longer marked as flash
        $this->assertFalse(isset($_SESSION['__ci_vars']['foo']));
    }

    public function testGetFlashKeysOnlyReturnsFlashKeys()
    {
        $session = $this->getInstance();
        $session->start();

        $session->setFlashdata('foo', 'bar');
        $session->set('bar', 'baz');

        $keys = $session->getFlashKeys();

        $this->assertTrue(in_array('foo', $keys));
        $this->assertFalse(in_array('bar', $keys));
    }

    public function testSetTempDataWorks()
    {
        $session = $this->getInstance();
        $session->start();

        $session->setTempdata('foo', 'bar', 300);
        $this->assertTrue((time() + 300) >= $_SESSION['__ci_vars']['foo']);
    }

    public function testSetTempDataArrayMultiTTL()
    {
        $session = $this->getInstance();
        $session->start();

        $time = time();

        $session->setTempdata([
            'foo' => 300,
            'bar' => 400,
            'baz' => 100
        ]);

        $this->assertTrue(($time + 300) <= $_SESSION['__ci_vars']['foo']);
        $this->assertTrue(($time + 400) <= $_SESSION['__ci_vars']['bar']);
        $this->assertTrue(($time + 100) <= $_SESSION['__ci_vars']['baz']);
    }

    public function testSetTempDataArraySingleTTL()
    {
        $session = $this->getInstance();
        $session->start();

        $time = time();

        $session->setTempdata(['foo', 'bar', 'baz'], null, 200);

        $this->assertTrue(($time + 200) <= $_SESSION['__ci_vars']['foo']);
        $this->assertTrue(($time + 200) <= $_SESSION['__ci_vars']['bar']);
        $this->assertTrue(($time + 200) <= $_SESSION['__ci_vars']['baz']);
    }

    /**
     * @group single
     */
    public function testGetTestDataReturnsAll()
    {
        $session = $this->getInstance();
        $session->start();

        $data = [
            'foo' => 'bar',
            'bar' => 'baz'
        ];

        $session->setTempdata($data);
        $session->set('baz', 'ballywhoo');

        $this->assertEquals($data, $session->getTempdata());
    }

    public function testGetTestDataReturnsSingle()
    {
        $session = $this->getInstance();
        $session->start();

        $data = [
            'foo' => 'bar',
            'bar' => 'baz'
        ];

        $session->setTempdata($data);

        $this->assertEquals('bar', $session->getTempdata('foo'));
    }

    public function testRemoveTempDataActuallyDeletes()
    {
        $session = $this->getInstance();
        $session->start();

        $data = [
            'foo' => 'bar',
            'bar' => 'baz'
        ];

        $session->setTempdata($data);
        $session->removeTempdata('foo');

        $this->assertEquals(['bar' => 'baz'], $session->getTempdata());
    }

    public function testUnMarkTempDataSingle()
    {
        $session = $this->getInstance();
        $session->start();

        $data = [
            'foo' => 'bar',
            'bar' => 'baz'
        ];

        $session->setTempdata($data);
        $session->unmarkTempdata('foo');

        $this->assertEquals(['bar' => 'baz'], $session->getTempdata());
    }

    public function testUnMarkTempDataArray()
    {
        $session = $this->getInstance();
        $session->start();

        $data = [
            'foo' => 'bar',
            'bar' => 'baz'
        ];

        $session->setTempdata($data);
        $session->unmarkTempdata(['foo', 'bar']);

        $this->assertEquals([], $session->getTempdata());
    }

    public function testGetTempdataKeys()
    {
        $session = $this->getInstance();
        $session->start();

        $data = [
            'foo' => 'bar',
            'bar' => 'baz'
        ];

        $session->setTempdata($data);
        $session->set('baz', 'ballywhoo');

        $this->assertEquals(['foo', 'bar'], $session->getTempKeys());
    }
}
