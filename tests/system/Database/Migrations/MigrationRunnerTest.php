<?php namespace CodeIgniter\Database;

use CodeIgniter\Exceptions\ConfigException;
use Config\Migrations;
use org\bovigo\vfs\vfsStream;
use CodeIgniter\Test\CIDatabaseTestCase;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;

/**
 * @group DatabaseLive
 */
class MigrationRunnerTest extends CIDatabaseTestCase
{
	protected $refresh = true;

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
			'id'        => 4,
			'version'   => 'abc123',
			'class'     => 'changesomething',
			'group'     => 'default',
			'namespace' => 'App',
			'time'      => time(),
			'batch'     => 1,
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

		$runner = $runner->setPath(TESTPATH . '_support/Database/SupportMigrations');

		$mig1 = (object)[
							'name'    => 'Some_migration',
							'path'    => TESTPATH . '_support/Database/SupportMigrations/20180124102301_Some_migration.php',
							'version' => '20180124102301',
							'class'   => 'App\Database\Migrations\Migration_some_migration',
						];
		$mig2 = (object)[
							'name'    => 'Another_migration',
							'path'    => TESTPATH . '_support/Database/SupportMigrations/20180124102302_Another_migration.php',
							'version' => '20180124102302',
							'class'   => 'App\Database\Migrations\Migration_another_migration',
						];

		$migrations = $runner->findMigrations();

		$this->assertCount(2, $migrations);
		$this->assertEquals($mig1, array_shift($migrations));
		$this->assertEquals($mig2, array_shift($migrations));
	}

	/**
	 * @expectedException        \CodeIgniter\Exceptions\ConfigException
	 * @expectedExceptionMessage Migrations have been loaded but are disabled or setup incorrectly.
	 */
	public function testMigrationThrowsDisabledException()
	{
		$config          = $this->config;
		$config->enabled = false;
		$runner          = new MigrationRunner($config);

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

	public function testVersionReturnsUpDownSuccess()
	{
		$forge = \Config\Database::forge();
		$forge->dropTable('foo', true);

		$config = $this->config;
		$runner = new MigrationRunner($config);
		$runner->setSilent(false);
		$runner->clearHistory();

		$runner = $runner->setPath(TESTPATH . '_support/Database/SupportMigrations');

		$version = $runner->version('20180124102301');

		$this->assertEquals('20180124102301', $version);
		$this->seeInDatabase('foo', ['key' => 'foobar']);

		$version = $runner->version(0);

		$this->assertEquals('0', $version);
		$this->assertFalse($this->db->tableExists('foo'));
	}

	public function testLatestSuccess()
	{
		$config = $this->config;
		$runner = new MigrationRunner($config);
		$runner->setSilent(false);
		$runner->clearHistory();

		$runner = $runner->setPath(TESTPATH . '_support/Database/SupportMigrations');

		$version = $runner->latest();

		$this->assertEquals('20180124102302', $version);
		$this->assertTrue(db_connect()->tableExists('foo'));

		$this->seeInDatabase('migrations', [
			'batch' => 1,
		]);
	}

	public function testVersionReturnsDownSuccess()
	{
		$config = $this->config;
		$runner = new MigrationRunner($config);
		$runner->setSilent(false);

		$runner = $runner->setPath(TESTPATH . '_support/Database/SupportMigrations');
		$runner->latest();

		$version = $runner->version(0);

		$this->assertEquals(0, $version);
		$this->assertFalse(db_connect()->tableExists('foo'));

		$history = $runner->getHistory();
		$this->assertEmpty($history);
	}

	public function testHistoryRecordsBatches()
	{
		$config = $this->config;
		$runner = new MigrationRunner($config);
		$runner->setSilent(false);
		$runner->clearHistory();

		$runner = $runner->setPath(TESTPATH . '_support/Database/SupportMigrations');

		$version = $runner->version('20180124102301');

		$this->assertEquals('20180124102301', $version);

		$history = $runner->getHistory('tests');
		$this->assertEquals(1, $history[0]['batch']);

		$version = $runner->version('20180124102302');

		$this->assertEquals('20180124102302', $version);

		$history = $runner->getHistory('tests');

		$this->assertEquals(1, $history[0]['batch']);
		$this->assertEquals(2, $history[1]['batch']);

		$this->seeInDatabase('migrations', [
			'batch' => 1,
		]);
	}

}
