<?php

class Kint_Parsers_Xml extends kintParser
{
	protected function _parse( & $variable )
	{
		try {
			if ( is_string( $variable ) && substr( $variable, 0, 5 ) === '<?xml' ) {
				$e   = libxml_use_internal_errors( true );
				$xml = simplexml_load_string( $variable );
				libxml_use_internal_errors( $e );
				if ( empty( $xml ) ) {
					return false;
				}
			} else {
				return false;
			}
		} catch ( Exception $e ) {
			return false;
		}

		$this->value = kintParser::factory( $xml )->extendedValue;
		$this->type  = 'XML';
	}
}
