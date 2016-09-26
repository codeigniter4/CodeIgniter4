<?php

class Kint_Parsers_Microtime extends kintParser
{
	private static $_times = array();
	private static $_laps  = array();

	protected function _parse( & $variable )
	{
		if ( !is_string( $variable ) || !preg_match( '[0\.[0-9]{8} [0-9]{10}]', $variable ) ) {
			return false;
		}

		list( $usec, $sec ) = explode( " ", $variable );

		$time = (float) $usec + (float) $sec;
		if ( KINT_PHP53 ) {
			$size = memory_get_usage( true );
		}

		# '@' is used to prevent the dreaded timezone not set error
		$this->value = @date( 'Y-m-d H:i:s', $sec ) . '.' . substr( $usec, 2, 4 );

		$numberOfCalls = count( self::$_times );
		if ( $numberOfCalls > 0 ) { # meh, faster than count($times) > 1
			$lap           = $time - end( self::$_times );
			self::$_laps[] = $lap;

			$this->value .= "\n<b>SINCE LAST CALL:</b> <b class=\"kint-microtime\">" . round( $lap, 4 ) . '</b>s.';
			if ( $numberOfCalls > 1 ) {
				$this->value .= "\n<b>SINCE START:</b> " . round( $time - self::$_times[0], 4 ) . 's.';
				$this->value .= "\n<b>AVERAGE DURATION:</b> "
					. round( array_sum( self::$_laps ) / $numberOfCalls, 4 ) . 's.';
			}
		}

		$unit = array( 'B', 'KB', 'MB', 'GB', 'TB' );
		if ( KINT_PHP53 ) {
			$this->value .= "\n<b>MEMORY USAGE:</b> " . $size . " bytes ("
				. round( $size / pow( 1024, ( $i = floor( log( $size, 1024 ) ) ) ), 3 ) . ' ' . $unit[ $i ] . ")";
		}

		self::$_times[] = $time;
		$this->type     = 'Stats';
	}

	/*
	function test() {
		d( 'start', microtime() );
		for ( $i = 0; $i < 10; $i++ ) {
			d(
				$duration = mt_rand( 0, 200000 ), // the reported duration will be larger because of Kint overhead
				usleep( $duration ),
				microtime()
	        );
		}
		dd(  );
	}
	 */
}