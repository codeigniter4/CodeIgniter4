<?php namespace CodeIgniter\Cache\Handlers;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
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
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2014-2019 British Columbia Institute of Technology (https://bcit.ca/)
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

class RedisHandlerTest extends \CodeIgniter\Test\CIUnitTestCase
{
	private $redisHandler;
	private static $key1 = 'key1';
	private static $key2 = 'key2';
	private static $key3 = 'key3';
	private static function getKeyArray()
	{
		return [
			self::$key1,
			self::$key2,
			self::$key3,
		];
	}

	private static $dummy = 'dymmy';
	private $config;

	protected function setUp(): void
	{
		parent::setUp();

		$this->config = new \Config\Cache();

		$this->redisHandler = new RedisHandler($this->config);
		if (! $this->redisHandler->isSupported())
		{
			$this->markTestSkipped('Not support redis');
		}

		$this->redisHandler->initialize();
	}

	public function tearDown(): void
	{
		foreach (self::getKeyArray() as $key)
		{
			$this->redisHandler->delete($key);
		}
	}

	public function testNew()
	{
		$this->assertInstanceOf(RedisHandler::class, $this->redisHandler);
	}

	public function testDestruct()
	{
		$this->redisHandler = new RedisHandler($this->config);
		$this->redisHandler->initialize();

		$this->assertInstanceOf(RedisHandler::class, $this->redisHandler);
	}

	public function testGet()
	{
		$this->redisHandler->save(self::$key1, 'value', 2);

		$this->assertSame('value', $this->redisHandler->get(self::$key1));
		$this->assertNull($this->redisHandler->get(self::$dummy));

		\CodeIgniter\CLI\CLI::wait(3);
		$this->assertNull($this->redisHandler->get(self::$key1));
	}

	public function testSave()
	{
		$this->assertTrue($this->redisHandler->save(self::$key1, 'value'));
	}

	public function testDelete()
	{
		$this->redisHandler->save(self::$key1, 'value');

		$this->assertTrue($this->redisHandler->delete(self::$key1));
		$this->assertFalse($this->redisHandler->delete(self::$dummy));
	}

	//FIXME: I don't like all Hash logic very much. It's wasting memory.
	//public function testIncrement()
	//{
	//}

	//public function testDecrement()
	//{
	//}

	public function testClean()
	{
		$this->redisHandler->save(self::$key1, 1);
		$this->redisHandler->save(self::$key2, 'value');

		$this->assertTrue($this->redisHandler->clean());
	}

	public function testGetCacheInfo()
	{
		$this->redisHandler->save(self::$key1, 'value');

		$this->assertIsArray($this->redisHandler->getCacheInfo());
	}

	public function testGetMetaData()
	{
		$time = time();
		$this->redisHandler->save(self::$key1, 'value');

		$this->assertNull($this->redisHandler->getMetaData(self::$dummy));

		$actual = $this->redisHandler->getMetaData(self::$key1);
		$this->assertLessThanOrEqual(60, $actual['expire'] - $time);
		$this->assertLessThanOrEqual(0, $actual['mtime'] - $time);
		$this->assertSame('value', $actual['data']);
	}

	public function testIsSupported()
	{
		$this->assertTrue($this->redisHandler->isSupported());
	}
}
