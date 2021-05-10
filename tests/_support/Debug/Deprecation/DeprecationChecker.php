<?php

namespace Tests\Support\Debug\Deprecation;

use CodeIgniter\Debug\Deprecation\Deprecation;

abstract class DeprecationChecker
{
	public function __construct()
	{
		Deprecation::checkDeprecatedInterface($this, MyDeprecatedInterface::class, MyReplacementInterface::class);
		Deprecation::checkDeprecatedTrait($this, MyDeprecatedTrait::class, MyReplacementTrait::class);
	}
}
