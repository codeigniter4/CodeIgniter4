<?php

namespace Tests\Support\Debug\Deprecation;

use CodeIgniter\Debug\Deprecation\Deprecation;

class MyDeprecatedClass
{
	public function __construct()
	{
		Deprecation::triggerForClass(self::class, MyReplacementClass::class);
	}
}
