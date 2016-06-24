<?php

class kintVariableData
{
	/** @var string */
	public $type;
	/** @var string */
	public $access;
	/** @var string */
	public $name;
	/** @var string */
	public $operator;
	/** @var int */
	public $size;
	/**
	 * @var kintVariableData[] array of kintVariableData objects or strings; displayed collapsed, each element from
	 * the array is a separate possible representation of the dumped var
	 */
	public $extendedValue;
	/** @var string inline value */
	public $value;

	/** @var kintVariableData[] array of alternative representations for same variable, don't use in custom parsers */
	public $_alternatives;

	/* *******************************************
	 * HELPERS
	 */

	protected static function _detectEncoding( $value )
	{
		$ret = null;
		if ( function_exists( 'mb_detect_encoding' ) ) {
			$mbDetected = mb_detect_encoding( $value );
			if ( $mbDetected === 'ASCII' ) return 'ASCII';
		}


		if ( !function_exists( 'iconv' ) ) {
			return !empty( $mbDetected ) ? $mbDetected : 'UTF-8';
		}

		$md5 = md5( $value );
		foreach ( Kint::$charEncodings as $encoding ) {
			# fuck knows why, //IGNORE and //TRANSLIT still throw notice
			if ( md5( @iconv( $encoding, $encoding, $value ) ) === $md5 ) {
				return $encoding;
			}
		}

		return 'ASCII';
	}

	/**
	 * returns whether the array:
	 *  1) is numeric and
	 *  2) in sequence starting from zero
	 *
	 * @param array $array
	 *
	 * @return bool
	 */
	protected static function _isSequential( array $array )
	{
		return array_keys( $array ) === range( 0, count( $array ) - 1 );
	}

	protected static function _strlen( $string, $encoding = null )
	{
		if ( function_exists( 'mb_strlen' ) ) {
			$encoding or $encoding = self::_detectEncoding( $string );
			return mb_strlen( $string, $encoding );
		} else {
			return strlen( $string );
		}
	}

	protected static function _substr( $string, $start, $end, $encoding = null )
	{
		if ( function_exists( 'mb_substr' ) ) {
			$encoding or $encoding = self::_detectEncoding( $string );
			return mb_substr( $string, $start, $end, $encoding );
		} else {
			return substr( $string, $start, $end );
		}
	}
}
