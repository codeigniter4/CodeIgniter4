<?php

abstract class KintObject
{
	/** @var string type of variable, can be set in inherited object or in static::parse() method */
	public $name = 'NOT SET';

	/** @var string quick variable value displayed inline */
	public $value;

	/**
	 * returns false or associative array - each key represents a tab in default view, values may be anything
	 *
	 * @param $variable
	 *
	 * @return mixed
	 */
	abstract public function parse( & $variable );
}