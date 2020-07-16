<?php namespace CodeIgniter\Database;

use CodeIgniter\Exceptions\ConfigException;
use CodeIgniter\Test\CIDatabaseTestCase;
use Config\Migrations;
use Config\Services;
use org\bovigo\vfs\vfsStream;

/**
 * @group DatabaseLive
 */
class MigrationRunnerTest extends CIDatabaseTestCase
{
	protected $refresh = true;

	protected $root;
	protected $start;
	protected $config;

	public function setUp(): void
	{
		parent::setUp();

		$this->root            = vfsStream::setup('root');
		$this->start           = $this->root->url() . '/';
		$this->config          = new Migrations();
		$this->config->enabled = true;

		Services::autoloader()->addNamespace('Tests\Support\MigrationTestMigrations', TESTPATH . '_support/MigrationTestMigrations');
	}

	public function testLoadsDefaultDatabaseWhenNoneSpecified()
	{
		$dbConfig = new \Config\Database();
		$runner   = new MigrationRunner($this->config);

		$db = $this->getPrivateProperty($runner, 'db');

		$this->assertInstanceOf(BaseConnection::class, $db);
		$this->assertEquals(
			($dbConfig->tests['DBDriver'] === 'SQLite3' ? WRITEPATH : '' ) . $dbConfig->tests['database'],
			$this->getPrivateProperty($db, 'database')
		);
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

		$this->assertEquals($history, (array) $runner->getHistory()[0]);
	}

	public function testGetHistoryReturnsEmptyArrayWithNoResults()
	{
		$runner = new MigrationRunner($this->config);

		$tableMaker = $this->getPrivateMethodInvoker($runner, 'ensureTable');
		$tableMaker();

		$this->assertEquals([], $runner->getHistory());
	}

	public function testGetMigrationNumberAllDigits()
	{
		$runner = new MigrationRunner($this->config);

		$method = $this->getPrivateMethodInvoker($runner, 'getMigrationNumber');

		$this->assertEquals('20190806235100', $method('20190806235100_Foo'));
	}

	public function testGetMigrationNumberDashes()
	{
		$runner = new MigrationRunner($this->config);

		$method = $this->getPrivateMethodInvoker($runner, 'getMigrationNumber');

		$this->assertEquals('2019-08-06-235100', $method('2019-08-06-235100_Foo'));
	}

	public function testGetMigrationNumberUnderscores()
	{
		$runner = new MigrationRunner($this->config);

		$method = $this->getPrivateMethodInvoker($runner, 'getMigrationNumber');

		$this->assertEquals('2019_08_06_235100', $method('2019_08_06_235100_Foo'));
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

		//      $runner->setPath($this->start);

		$this->assertEquals([], $runner->findMigrations());
	}

	public function testFindMigrationsSuccessTimestamp()
	{
		$config       = $this->config;
		$config->type = 'timestamp';
		$runner       = new MigrationRunner($config);

		$runner = $runner->setNamespace('Tests\Support\MigrationTestMigrations');

		$mig1      = (object)[
								 'name'      => 'Some_migration',
								 'path'      => TESTPATH . '_support/MigrationTestMigrations/Database/Migrations/2018-01-24-102301_Some_migration.php',
								 'version'   => '2018-01-24-102301',
								 'class'     => 'Tests\Support\MigrationTestMigrations\Database\Migrations\Migration_some_migration',
								 'namespace' => 'Tests\Support\MigrationTestMigrations',
							 ];
		$mig1->uid = $runner->getObjectUid($mig1);

		$mig2      = (object)[
								 'name'      => 'Another_migration',
								 'path'      => TESTPATH . '_support/MigrationTestMigrations/Database/Migrations/2018-01-24-102302_Another_migration.php',
								 'version'   => '2018-01-24-102302',
								 'class'     => 'Tests\Support\MigrationTestMigrations\Database\Migrations\Migration_another_migration',
								 'namespace' => 'Tests\Support\MigrationTestMigrations',
								 'uid'       => '20180124102302Tests\Support\MigrationTestMigrations\Database\Migrations\Migration_another_migration',
							 ];
		$mig1->uid = $runner->getObjectUid($mig1);

		$migrations = $runner->findMigrations();

		$this->assertCount(2, $migrations);
		$this->assertEquals($mig1, array_shift($migrations));
		$this->assertEquals($mig2, array_shift($migrations));
	}

	public function testMigrationThrowsDisabledException()
	{
		$this->expectException('CodeIgniter\Exceptions\ConfigException');
		$this->expectExceptionMessage('Migrations have been loaded but are disabled or setup incorrectly.');

		$config          = $this->config;
		$config->enabled = false;
		$runner          = new MigrationRunner($config);

		$runner->setSilent(false);

		$runner = $runner->setNamespace('Tests\Support\MigrationTestMigrations');

		vfsStream::copyFromFileSystem(
			TESTPATH . '_support/MigrationTestMigrations/Database/Migrations',
			$this->root
		);

		$this->expectException(ConfigException::class);
		$this->expectExceptionMessage('Migrations have been loaded but are disabled or setup incorrectly.');

		$runner->latest();
	}

	public function testVersionReturnsUpDownSuccess()
	{
		$forge = \Config\Database::forge();
		$forge->dropTable('foo', true);

		$config = $this->config;
		$runner = new MigrationRunner($config);
		$runner->setSilent(false);
		$runner->clearHistory();

		$runner = $runner->setNamespace('Tests\Support\MigrationTestMigrations');

		$runner->latest();
		$version = $runner->getBatchEnd($runner->getLastBatch());

		$this->assertEquals('2018-01-24-102302', $version);
		$this->seeInDatabase('foo', ['key' => 'foobar']);

		$runner->regress(0);
		$version = $runner->getBatchEnd($runner->getLastBatch());

		$this->assertEquals('0', $version);
		$this->assertFalse($this->db->tableExists('foo'));
	}

	public function testProgressSuccess()
	{
		$config = $this->config;
		$runner = new MigrationRunner($config);
		$runner->setSilent(false);
		$runner->clearHistory();

		$runner = $runner->setNamespace('Tests\Support\MigrationTestMigrations');

		$runner->latest();
		$version = $runner->getBatchEnd($runner->getLastBatch());

		$this->assertEquals('2018-01-24-102302', $version);
		$this->assertTrue(db_connect()->tableExists('foo'));

		$this->seeInDatabase('migrations', [
			'batch' => 1,
		]);
	}

	public function testRegressSuccess()
	{
		$config = $this->config;
		$runner = new MigrationRunner($config);
		$runner->setSilent(false);

		$runner = $runner->setNamespace('Tests\Support\MigrationTestMigrations');
		$runner->latest();

		$runner->regress();
		$version = $runner->getBatchEnd($runner->getLastBatch());

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
		$this->resetTables();

		$runner = $runner->setNamespace('Tests\Support\MigrationTestMigrations');

		$runner->latest();
		$version = $runner->getBatchEnd($runner->getLastBatch());

		$this->assertEquals('2018-01-24-102302', $version);

		$history = $runner->getHistory('tests');

		$this->assertEquals(1, $history[0]->batch);

		$this->assertEquals(1, $history[0]->batch);
		$this->assertEquals(1, $history[1]->batch);

		$this->seeInDatabase('migrations', [
			'batch' => 1,
		]);
	}

	public function testGetBatchVersions()
	{
		$config = $this->config;
		$runner = new MigrationRunner($config);
		$runner->setSilent(false);
		$runner->clearHistory();
		$this->resetTables();

		$runner = $runner->setNamespace('Tests\Support\MigrationTestMigrations');

		$runner->latest();

		$this->assertEquals('2018-01-24-102301', $runner->getBatchStart(1));
		$this->assertEquals('2018-01-24-102302', $runner->getBatchEnd(1));
	}

	protected function resetTables()
	{
		$db    = db_connect();
		$forge = Config::forge();

		$tables = $db->listTables();
		foreach ($tables as $table)
		{
			$table = str_replace('db_', '', $table);

			$forge->dropTable($table, true);
		}
	}
}
