<?php namespace CodeIgniter\Cache\Handlers;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2017 British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	2014-2017 British Columbia Institute of Technology (https://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */
class AerospikeHandlerTest extends \CIUnitTestCase
{
	private $aerospikeHandler;
	private static $key1 = 'key1';
	private static $key2 = 'key2';
	private static $key3 = 'key3';
	private static function getKeyArray()
	{
		return [
			self::$key1, self::$key2, self::$key3
		];
	}

	private static $dummy = 'dymmy';
	private $config;

	public function setUp()
	{
		parent::setUp();

		$this->config = new \Config\Cache();

		$this->aerospikeHandler = new AerospikeHandler($this->config->aerospike);

		if (!$this->aerospikeHandler->isSupported())
		{
			$this->markTestSkipped('Not support aerospike');
		}

		$this->aerospikeHandler->initialize();
	}

	public function tearDown()
	{
		foreach (self::getKeyArray() as $key)
		{
			$this->aerospikeHandler->delete($key);
		}
	}

	public function testNew()
	{
		$this->assertInstanceOf(AerospikeHandler::class, $this->aerospikeHandler);
	}

	public function testDestruct()
	{
		$this->aerospikeHandler = new AerospikeHandler($this->config->aerospike);
		$this->aerospikeHandler->initialize();

		$this->assertInstanceOf(AerospikeHandler::class, $this->aerospikeHandler);
	}

	public function testGet()
	{
		$this->aerospikeHandler->save(self::$key1, 'value', 1);

		$this->assertSame('value', $this->aerospikeHandler->get(self::$key1));
		$this->assertFalse($this->aerospikeHandler->get(self::$dummy));

		\CodeIgniter\CLI\CLI::wait(2);
		$this->assertFalse($this->aerospikeHandler->get(self::$key1));
	}

	public function testSave()
	{
		$this->assertTrue($this->aerospikeHandler->save(self::$key1, 'value'));
	}

	public function testDelete()
	{
		$this->aerospikeHandler->save(self::$key1, 'value');

		$this->assertTrue($this->aerospikeHandler->delete(self::$key1));
		$this->assertFalse($this->aerospikeHandler->delete(self::$dummy));
	}

	public function testClean()
	{
		$this->aerospikeHandler->save(self::$key1, 1);
		$this->aerospikeHandler->save(self::$key2, 'value');

		$this->assertTrue($this->aerospikeHandler->clean());
	}

	public function testGetCacheInfo()
	{
		$this->aerospikeHandler->save(self::$key1, 'value');

		$this->assertInternalType('array', $this->aerospikeHandler->getCacheInfo());
	}

	public function testGetMetaData()
	{
		$time = time();
		$this->aerospikeHandler->save(self::$key1, 'value');

		$this->assertFalse($this->aerospikeHandler->getMetaData(self::$dummy));

		$actual = $this->aerospikeHandler->getMetaData(self::$key1);
		$this->assertLessThanOrEqual(60, $actual['expire'] - $time);
		$this->assertLessThanOrEqual(0, $actual['mtime'] - $time);
		$this->assertSame('value', $actual['data']);
	}

	public function testIsSupported()
	{
		$this->assertTrue($this->aerospikeHandler->isSupported());
	}
}