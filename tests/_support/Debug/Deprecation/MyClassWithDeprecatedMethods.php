<?php

namespace Tests\Support\Debug\Deprecation;

use CodeIgniter\Debug\Deprecation\DeprecatedClassMethodTrait;
use CodeIgniter\Debug\Deprecation\Deprecation;

class MyClassWithDeprecatedMethods
{
	use DeprecatedClassMethodTrait;

	private $deprecatedMethods = [
		'implicitlyDeprecatedProtected' => self::class . '::replacement',
		'implicitlyDeprecatedPrivate'   => self::class . '::replacement',
	];

	private static $deprecatedStaticMethods = [
		'staticDeprecatedProtected' => self::class . '::staticReplacement',
		'staticDeprecatedPrivate'   => self::class . '::staticReplacement',
	];

	private static $methodAccessExclusions = [
		'barred',
		'staticBarred',
	];

	public function withDeprecatedParams($deprecated1 = 1, $deprecated2 = 2)
	{
		if (func_num_args() > 0)
		{
			Deprecation::triggerForMethodParameter(['deprecated1', 'deprecated2'], __METHOD__);
		}

		return 3;
	}

	public function withDeprecatedParam($notDeprecated, $deprecated = true)
	{
		if (func_num_args() > 1)
		{
			Deprecation::triggerForMethodParameter('deprecated', __METHOD__);
		}

		return $notDeprecated;
	}

	public function explicitlyDeprecated()
	{
		Deprecation::triggerForMethod(__METHOD__, self::class . '::replacement');
		$this->replacement();
	}

	public function replacement()
	{
	}

	private function barred()
	{
	}

	private function notBarred()
	{
	}

	public static function staticReplacement()
	{
	}

	protected function implicitlyDeprecatedProtected()
	{
	}

	private function implicitlyDeprecatedPrivate()
	{
	}

	protected static function staticDeprecatedProtected()
	{
	}

	private static function staticDeprecatedPrivate()
	{
	}

	protected static function staticBarred()
	{
	}

	private static function staticNotBarred()
	{
	}
}
