<?php

use Cache\IntegrationTests\CachePoolTest as TestCase;
use CodeIgniter\Psr\Cache\Pool;

class CachePoolTest extends TestCase
{
	public function createCachePool()
	{
		return new Pool();
	}
}
