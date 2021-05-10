<?php

namespace Tests\Support\Debug\Deprecation;

use CodeIgniter\Debug\Deprecation\DeprecatedClassPropertyTrait;

class MyClassWithDeprecatedProps
{
	use DeprecatedClassPropertyTrait;

	public $replacement = 1;

	protected $deprecatedProtected = 2;

	protected $barredToo = 6;

	private $deprecatedPrivate = 3;

	private $barred = 4;

	private $notBarred = 0;

	private $deprecatedProperties = [
		'deprecatedProtected' => '$replacement',
		'deprecatedPrivate'   => '$replacement',
	];

	private $deprecatedSettableProperties = [
		'deprecatedProtected',
		'deprecatedPrivate',
	];

	private static $propertyAccessExclusions = [
		'barred',
		'barredToo',
	];
}
