<?php namespace CodeIgniter\Database;

use Config\Migrations;
use org\bovigo\vfs\vfsStream;
use CodeIgniter\Test\CIDatabaseTestCase;

class MigrationRunnerTest extends CIDatabaseTestCase
{
	protected $root;
	protected $start;

	public function setUp()
	{
		parent::setUp();

		$this->root  = vfsStream::setup('root');
		$this->start = $this->root->url() . '/';
	}

	/**
	 * @expectedException \CodeIgniter\Exceptions\ConfigException
	 */
	public function testThrowsOnInvalidMigrationType()
	{
		$config       = new Migrations();
		$config->type = 'narwhal';

		$runner = new MigrationRunner($config);
	}

	public function testLoadsDefaultDatabaseWhenNoneSpecified()
	{
		$dbConfig = new \Config\Database();
		$config   = new Migrations();
		$runner   = new MigrationRunner($config);

		$db = $this->getPrivateProperty($runner, 'db');

		$this->assertInstanceOf(BaseConnection::class, $db);
		$this->assertEquals($dbConfig->tests['database'], $this->getPrivateProperty($db, 'database'));
		$this->assertEquals($dbConfig->tests['DBDriver'], $this->getPrivateProperty($db, 'DBDriver'));
	}

	public function testGetCliMessages()
	{
		$config = new Migrations();
		$runner = new MigrationRunner($config);

		$messages = [
			'foo',
			'bar',
		];

		$this->setPrivateProperty($runner, 'cliMessages', $messages);

		$this->assertEquals($messages, $runner->getCliMessages());
	}

	public function testGetHistory()
	{
		$config = new Migrations();
		$runner = new MigrationRunner($config);

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
		$config = new Migrations();
		$runner = new MigrationRunner($config);

		$tableMaker = $this->getPrivateMethodInvoker($runner, 'ensureTable');
		$tableMaker();

		$this->assertEquals([], $runner->getHistory());
	}

	public function testGetMigrationNumber()
	{
		$config = new Migrations();
		$runner = new MigrationRunner($config);

		$method = $this->getPrivateMethodInvoker($runner, 'getMigrationNumber');

		$this->assertEquals('0123456', $method('0123456_Foo'));
	}

	public function testGetMigrationNumberReturnsZeroIfNoneFound()
	{
		$config = new Migrations();
		$runner = new MigrationRunner($config);

		$method = $this->getPrivateMethodInvoker($runner, 'getMigrationNumber');

		$this->assertEquals('0', $method('Foo'));
	}

	public function testSetSilentStoresValue()
	{
		$config = new Migrations();
		$runner = new MigrationRunner($config);

		$runner->setSilent(true);
		$this->assertTrue($this->getPrivateProperty($runner, 'silent'));

		$runner->setSilent(false);
		$this->assertFalse($this->getPrivateProperty($runner, 'silent'));
	}

	public function testSetNameStoresValue()
	{
		$config = new Migrations();
		$runner = new MigrationRunner($config);

		$runner->setName('foo');
		$this->assertEquals('foo', $this->getPrivateProperty($runner, 'name'));
	}

	public function testSetGroupStoresValue()
	{
		$config = new Migrations();
		$runner = new MigrationRunner($config);

		$runner->setGroup('foo');
		$this->assertEquals('foo', $this->getPrivateProperty($runner, 'group'));
	}

	public function testSetNamespaceStoresValue()
	{
		$config = new Migrations();
		$runner = new MigrationRunner($config);

		$runner->setNamespace('foo');
		$this->assertEquals('foo', $this->getPrivateProperty($runner, 'namespace'));
	}

	public function testFindMigrationsReturnsEmptyArrayWithNoneFound()
	{
		$config       = new Migrations();
		$config->type = 'timestamp';
		$runner       = new MigrationRunner($config);

		$runner->setPath($this->start);

		$this->assertEquals([], $runner->findMigrations());
	}

	public function testFindMigrationsSuccessTimestamp()
	{
		$config       = new Migrations();
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
		$this->assertEquals($mig1, array_shift($migrations));
		$this->assertEquals($mig2, array_shift($migrations));
	}

	public function testFindMigrationsSuccessOrder()
	{
		$config       = new Migrations();
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

		$this->assertEquals($mig1, array_shift($migrations));
		$this->assertEquals($mig2, array_shift($migrations));
	}

	/**
	 * @expectedException        \RuntimeException
	 * @expectedExceptionMessage There is a gap in the migration sequence near version number:  002
	 */
	public function testVersionThrowsMigrationGapException()
	{
		$config       = new Migrations();
		$config->type = 'sequential';
		$runner       = new MigrationRunner($config);

		$runner = $runner->setPath($this->start);

		vfsStream::newFile('002_some_migration.php')->at($this->root);

		$version = $runner->version(0);

		$this->assertEquals($version, '002');
	}

	public function testVersionReturnsTrueWhenNothingToDo()
	{
		$config       = new Migrations();
		$config->type = 'sequential';
		$runner       = new MigrationRunner($config);

		$runner = $runner->setPath($this->start);

		vfsStream::newFile('001_some_migration.php')->at($this->root);

		$version = $runner->version(0);

		$this->assertTrue($version);
	}

	/**
	 * @expectedException        \RuntimeException
	 * @expectedExceptionMessage The migration class "App\Database\Migrations\Migration_some_migration" could not be found.
	 */
	public function testVersionWithNoClassInFile()
	{
		$config       = new Migrations();
		$config->type = 'sequential';
		$runner       = new MigrationRunner($config);
		$runner->setSilent(false);

		$runner = $runner->setPath($this->start);

		vfsStream::newFile('001_some_migration.php')->at($this->root);

		$version = $runner->version(1);

		$this->assertEquals('001', $version);
	}

	public function testVersionReturnsUpDownSuccess()
	{
		$config       = new Migrations();
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

		$this->assertTrue($version);
		$this->assertFalse(db_connect()->tableExists('foo'));
	}

	public function testLatestSuccess()
	{
		$config       = new Migrations();
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

	public function testCurrentSuccess()
	{
		$config                 = new Migrations();
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
