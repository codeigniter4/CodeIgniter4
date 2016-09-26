<?php

class Kint_Parsers_Timestamp extends kintParser
{
	private static function _fits( $variable )
	{
		if ( !is_string( $variable ) && !is_int( $variable ) ) return false;

		$len = strlen( (int) $variable );
		return
			(
				$len === 9 || $len === 10 # a little naive
				|| ( $len === 13 && substr( $variable, -3 ) === '000' ) # also handles javascript micro timestamps
			)
			&& ( (string) (int) $variable == $variable );
	}


	protected function _parse( & $variable )
	{
		if ( !self::_fits( $variable ) ) return false;

		$var = strlen( $variable ) === 13 ? substr( $variable, 0, -3 ) : $variable;

		$this->type = 'timestamp';
		# avoid dreaded "Timezone must be set" error
		$this->value = @date( 'Y-m-d H:i:s', $var );
	}
}