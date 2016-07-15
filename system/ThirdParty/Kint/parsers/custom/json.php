<?php

class Kint_Parsers_Json extends kintParser
{
	protected function _parse( & $variable )
	{
		if ( !KINT_PHP53
			|| !is_string( $variable )
			|| !isset( $variable{0} ) || ( $variable{0} !== '{' && $variable{0} !== '[' )
			|| ( $json = json_decode( $variable, true ) ) === null
		) return false;

		$val = (array) $json;
		if ( empty( $val ) ) return false;

		$this->value = kintParser::factory( $val )->extendedValue;
		$this->type  = 'JSON';
	}
}