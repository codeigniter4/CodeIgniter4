<?php

class Kint_Parsers_objectIterateable extends kintParser
{
	protected function _parse( & $variable )
	{
		if ( !KINT_PHP53
			|| !is_object( $variable )
			|| !$variable instanceof Traversable
			|| stripos( get_class( $variable ), 'zend' ) !== false // zf2 PDO wrapper does not play nice
		) return false;


		$arrayCopy = iterator_to_array( $variable, true );

		if ( $arrayCopy === false ) return false;

		$this->value = kintParser::factory( $arrayCopy )->extendedValue;
		$this->type  = 'Iterator contents';
		$this->size  = count( $arrayCopy );
	}
}