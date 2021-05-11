<?php

use Cache\IntegrationTests\CachePoolTest as TestCase;
use CodeIgniter\I18n\Time;
use CodeIgniter\Psr\Cache\Pool;
use Config\Services;

class CachePoolTest extends TestCase
{
	public function createCachePool()
	{
		Services::resetSingle('cache');
		Time::setTestNow(null);
		return new Pool();
	}
}
