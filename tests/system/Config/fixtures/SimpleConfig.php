<?php

class SimpleConfig extends \CodeIgniter\Config\BaseConfig
{

	public $QZERO;
	public $QZEROSTR;
	public $QEMPTYSTR;
	public $QFALSE;
	public $first  = 'foo';
	public $second = 'bar';
	public $FOO;
	public $onedeep;
	public $default = [
		'name' => null,
	];
	public $simple  = [
		'name' => null,
	];
	// properties for environment over-ride testing
	public $alpha   = 'one';
	public $bravo   = 'two';
	public $charlie = 'three';
	public $delta   = 'four';
	public $echo    = '';
	public $foxtrot = 'false';
	public $golf    = 18;
	public $crew    = [
		'captain' => 'Kirk',
		'science' => 'Spock',
		'doctor'  => 'Bones',
		'comms'   => 'Uhuru',
	];

}
