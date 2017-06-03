<?php

class RegistrarConfig extends \CodeIgniter\Config\BaseConfig
{
	public $foo = 'bar';
	public $bar = [
		'baz'
	];

	protected $registrars = [
		\Tests\Support\Config\Registrar::class
	];
}
