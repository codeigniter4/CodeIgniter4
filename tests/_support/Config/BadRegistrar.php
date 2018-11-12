<?php namespace Tests\Support\Config;

/**
 * Class BadRegistrar
 *
 * Doesn't provides a basic registrar class for testing BaseConfig registration functions,
 * because it doesn't return an associative array
 */

class BadRegistrar
{

	public static function RegistrarConfig2()
	{
		return 'I am not worthy';
	}

}
