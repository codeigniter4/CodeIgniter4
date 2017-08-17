<?php

class RegistrarConfig2 extends \CodeIgniter\Config\BaseConfig
{
	public $foo = 'bar';
	public $bar = [
		'baz'
	];

	protected $registrars = [
		\Tests\Support\Config\BadRegistrar::class
	];
}
