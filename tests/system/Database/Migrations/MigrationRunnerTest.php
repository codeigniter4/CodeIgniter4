<?php namespace CodeIgniter\Database;

use CodeIgniter\Exceptions\ConfigException;
use Config\Migrations;
use org\bovigo\vfs\vfsStream;
use CodeIgniter\Test\CIDatabaseTestCase;

/**
 * @group DatabaseLive
 */
class MigrationRunnerTest extends CIDatabaseTestCase
{
	protected $root;
	protected $start;
	protected $config;

	public function setUp()
	{
		parent::setUp();

		$this->root            = vfsStream::setup('root');
		$this->start           = $this->root->url() . '/';
		$this->config          = new Migrations();
		$this->config->enabled = true;
	}

	/**
	 * @expectedException \CodeIgniter\Exceptions\ConfigException
	 */
	public function testThrowsOnInvalidMigrationType()
	{
		$config       = $this->config;
		$config->type = 'narwhal';

		$runner = new MigrationRunner($config);
	}

	public function testLoadsDefaultDatabaseWhenNoneSpecified()
	{
		$dbConfig = new \Config\Database();
		$runner   = new MigrationRunner($this->config);

		$db = $this->getPrivateProperty($runner, 'db');

		$this->assertInstanceOf(BaseConnection::class, $db);
		$this->assertEquals($dbConfig->tests['database'], $this->getPrivateProperty($db, 'database'));
		$this->assertEquals($dbConfig->tests['DBDriver'], $this->getPrivateProperty($db, 'DBDriver'));
	}

	public function testGetCliMessages()
	{
		$runner = new MigrationRunner($this->config);

		$messages = [
			'foo',
			'bar',
		];

		$this->setPrivateProperty($runner, 'cliMessages', $messages);

		$this->assertEquals($messages, $runner->getCliMessages());
	}

	public function testGetHistory()
	{
		$runner = new MigrationRunner($this->config);

		$tableMaker = $this->getPrivateMethodInvoker($runner, 'ensureTable');
		$tableMaker();

		$history = [
			'version'   => 'abc123',
			'name'      => 'changesomething',
			'group'     => 'default',
			'namespace' => 'App',
			'time'      => time(),
		];

		$this->hasInDatabase('migrations', $history);

		$this->assertEquals($history, $runner->getHistory()[0]);
	}

	public function testGetHistoryReturnsEmptyArrayWithNoResults()
	{
		$runner = new MigrationRunner($this->config);

		$tableMaker = $this->getPrivateMethodInvoker($runner, 'ensureTable');
		$tableMaker();

		$this->assertEquals([], $runner->getHistory());
	}

	public function testGetMigrationNumber()
	{
		$runner = new MigrationRunner($this->config);

		$method = $this->getPrivateMethodInvoker($runner, 'getMigrationNumber');

		$this->assertEquals('0123456', $method('0123456_Foo'));
	}

	public function testGetMigrationNumberReturnsZeroIfNoneFound()
	{
		$runner = new MigrationRunner($this->config);

		$method = $this->getPrivateMethodInvoker($runner, 'getMigrationNumber');

		$this->assertEquals('0', $method('Foo'));
	}

	public function testSetSilentStoresValue()
	{
		$runner = new MigrationRunner($this->config);

		$runner->setSilent(true);
		$this->assertTrue($this->getPrivateProperty($runner, 'silent'));

		$runner->setSilent(false);
		$this->assertFalse($this->getPrivateProperty($runner, 'silent'));
	}

	public function testSetNameStoresValue()
	{
		$runner = new MigrationRunner($this->config);

		$runner->setName('foo');
		$this->assertEquals('foo', $this->getPrivateProperty($runner, 'name'));
	}

	public function testSetGroupStoresValue()
	{
		$runner = new MigrationRunner($this->config);

		$runner->setGroup('foo');
		$this->assertEquals('foo', $this->getPrivateProperty($runner, 'group'));
	}

	public function testSetNamespaceStoresValue()
	{
		$runner = new MigrationRunner($this->config);

		$runner->setNamespace('foo');
		$this->assertEquals('foo', $this->getPrivateProperty($runner, 'namespace'));
	}

	public function testFindMigrationsReturnsEmptyArrayWithNoneFound()
	{
		$config       = $this->config;
		$config->type = 'timestamp';
		$runner       = new MigrationRunner($config);

		$runner->setPath($this->start);

		$this->assertEquals([], $runner->findMigrations());
	}

	public function testFindMigrationsSuccessTimestamp()
	{
		$config       = $this->config;
		$config->type = 'timestamp';
		$runner       = new MigrationRunner($config);

		$runner = $runner->setPath($this->start);

		vfsStream::newFile('20180124102301_some_migration.php')->at($this->root);
		vfsStream::newFile('20180124082302_another_migration.php')->at($this->root); // should be first
		vfsStream::newFile('20180124082303_another_migration.py')->at($this->root); // shouldn't be included
		vfsStream::newFile('201801240823_another_migration.py')->at($this->root); // shouldn't be included

		$mig1 = (object)[
							'name'    => 'some_migration',
							'path'    => 'vfs://root//20180124102301_some_migration.php',
							'version' => '20180124102301',
						];
		$mig2 = (object)[
							'name'    => 'another_migration',
							'path'    => 'vfs://root//20180124082302_another_migration.php',
							'version' => '20180124082302',
						];

		$migrations = $runner->findMigrations();

		$this->assertCount(2, $migrations);
		$this->assertEquals($mig2, array_shift($migrations));
		$this->assertEquals($mig1, array_shift($migrations));
	}

	public function testFindMigrationsSuccessOrder()
	{
		$config       = $this->config;
		$config->type = 'sequential';
		$runner       = new MigrationRunner($config);

		$runner = $runner->setPath($this->start);

		vfsStream::newFile('002_some_migration.php')->at($this->root);
		vfsStream::newFile('001_another_migration.php')->at($this->root); // should be first
		vfsStream::newFile('003_another_migration.py')->at($this->root); // shouldn't be included
		vfsStream::newFile('004_another_migration.py')->at($this->root); // shouldn't be included

		$mig1 = (object)[
							'name'    => 'some_migration',
							'path'    => 'vfs://root//002_some_migration.php',
							'version' => '002',
						];
		$mig2 = (object)[
							'name'    => 'another_migration',
							'path'    => 'vfs://root//001_another_migration.php',
							'version' => '001',
						];

		$migrations = $runner->findMigrations();

		$this->assertEquals($mig2, array_shift($migrations));
		$this->assertEquals($mig1, array_shift($migrations));
	}

	/**
	 * @expectedException           \CodeIgniter\Exceptions\ConfigException
	 * @expectedExceptionMessage    Migrations have been loaded but are disabled or setup incorrectly.
	 */
	public function testMigrationThrowsDisabledException()
	{
		$config = $this->config;
		$config->type = 'sequential';
		$config->enabled = false;
		$runner = new MigrationRunner($config);

		$runner->setSilent(false);

		$runner = $runner->setPath($this->start);

		vfsStream::copyFromFileSystem(
			TESTPATH . '_support/Database/SupportMigrations',
			$this->root
		);

		$this->expectException(ConfigException::class);
		$this->expectExceptionMessage('Migrations have been loaded but are disabled or setup incorrectly.');

		$runner->version(1);
	}

	/**
	 * @expectedException        \RuntimeException
	 * @expectedExceptionMessage There is a gap in the migration sequence near version number:  002
	 */

	public function testVersionThrowsMigrationGapException()
	{
		$config       = $this->config;
		$config->type = 'sequential';
		$runner       = new MigrationRunner($config);

		$runner = $runner->setPath($this->start);

		vfsStream::newFile('002_some_migration.php')->at($this->root);

		$version = $runner->version(0);

		$this->assertFalse($version);
	}

	public function testVersionReturnsFalseWhenNothingToDo()
	{
		$config       = $this->config;
		$config->type = 'sequential';
		$runner       = new MigrationRunner($config);

		$runner = $runner->setPath($this->start);

		vfsStream::newFile('001_some_migration.php')->at($this->root);

		$version = $runner->version(0);

		$this->assertFalse($version);
	}

	/**
	 * @expectedException        \RuntimeException
	 * @expectedExceptionMessage The migration class "App\Database\Migrations\Migration_some_migration" could not be found.
	 */
	public function testVersionWithNoClassInFile()
	{
		$config       = $this->config;
		$config->type = 'sequential';
		$runner       = new MigrationRunner($config);
		$runner->setSilent(false);

		$runner = $runner->setPath($this->start);

		vfsStream::newFile('001_some_migration.php')->at($this->root);

		$version = $runner->version(1);

		$this->assertFalse($version);
	}

	public function testVersionReturnsUpDownSuccess()
	{
		$config       = $this->config;
		$config->type = 'sequential';
		$runner       = new MigrationRunner($config);
		$runner->setSilent(false);

		$runner = $runner->setPath($this->start);

		vfsStream::copyFromFileSystem(
			TESTPATH . '_support/Database/SupportMigrations',
			$this->root
		);

		$version = $runner->version(1);

		$this->assertEquals('001', $version);
		$this->seeInDatabase('foo', ['key' => 'foobar']);

		$version = $runner->version(0);

		$this->assertEquals('000', $version);
		$this->assertFalse(db_connect()->tableExists('foo'));
	}

	public function testLatestSuccess()
	{
		$config       = $this->config;
		$config->type = 'sequential';
		$runner       = new MigrationRunner($config);
		$runner->setSilent(false);

		$runner = $runner->setPath($this->start);

		vfsStream::copyFromFileSystem(
			TESTPATH . '_support/Database/SupportMigrations',
			$this->root
		);

		$version = $runner->latest();

		$this->assertEquals('001', $version);
		$this->assertTrue(db_connect()->tableExists('foo'));
	}

	public function testVersionReturnsDownSuccess()
	{
		$config       = $this->config;
		$config->type = 'sequential';
		$runner       = new MigrationRunner($config);
		$runner->setSilent(false);

		$runner = $runner->setPath($this->start);

		vfsStream::copyFromFileSystem(
			TESTPATH . '_support/Database/SupportMigrations',
			$this->root
		);

		$version = $runner->version(0);

		$this->assertEquals('000', $version);
		$this->assertFalse(db_connect()->tableExists('foo'));
	}

	public function testCurrentSuccess()
	{
		$config                 = $this->config;
		$config->type           = 'sequential';
		$config->currentVersion = 1;
		$runner                 = new MigrationRunner($config);
		$runner->setSilent(false);

		$runner = $runner->setPath($this->start);

		vfsStream::copyFromFileSystem(
			TESTPATH . '_support/Database/SupportMigrations',
			$this->root
		);

		$version = $runner->current();

		$this->assertEquals('001', $version);
		$this->assertTrue(db_connect()->tableExists('foo'));
	}
}
