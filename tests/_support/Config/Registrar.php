<?php namespace Tests\Support\Config;

/**
 * Class Registrar
 *
 * Provides a basic registrar class for testing BaseConfig registration functions.
 */

class Registrar
{

	public static function RegistrarConfig()
	{
		return [
			'bar'    => [
				'first',
				'second',
			],
			'format' => 'nice',
			'fruit'  => [
				'apple',
				'banana',
			],
		];
	}

}
