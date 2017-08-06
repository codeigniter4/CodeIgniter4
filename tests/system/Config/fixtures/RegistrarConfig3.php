<?php

class RegistrarConfig3 extends \CodeIgniter\Config\BaseConfig
{
	public $foo = 'bar';
	public $bar = [
		'baz'
	];

	protected $registrars = [
		\Tests\Support\Config\WorseRegistrar::class
	];
}
