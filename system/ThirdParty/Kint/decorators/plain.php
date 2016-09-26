<?php

class Kint_Decorators_Plain
{
	private static $_enableColors;

	private static $_cliEffects      = array(
		# effects
		'bold'             => '1', 'dark' => '2',
		'italic'           => '3', 'underline' => '4',
		'blink'            => '5', 'reverse' => '7',
		'concealed'        => '8', 'default' => '39',

		# colors
		'black'            => '30', 'red' => '31',
		'green'            => '32', 'yellow' => '33',
		'blue'             => '34', 'magenta' => '35',
		'cyan'             => '36', 'light_gray' => '37',
		'dark_gray'        => '90', 'light_red' => '91',
		'light_green'      => '92', 'light_yellow' => '93',
		'light_blue'       => '94', 'light_magenta' => '95',
		'light_cyan'       => '96', 'white' => '97',

		# backgrounds
		'bg_default'       => '49', 'bg_black' => '40',
		'bg_red'           => '41', 'bg_green' => '42',
		'bg_yellow'        => '43', 'bg_blue' => '44',
		'bg_magenta'       => '45', 'bg_cyan' => '46',
		'bg_light_gray'    => '47', 'bg_dark_gray' => '100',
		'bg_light_red'     => '101', 'bg_light_green' => '102',
		'bg_light_yellow'  => '103', 'bg_light_blue' => '104',
		'bg_light_magenta' => '105', 'bg_light_cyan' => '106',
		'bg_white'         => '107',
	);
	private static $_utfSymbols      = array(
		'┌', '═', '┐',
		'│',
		'└', '─', '┘',
	);
	private static $_winShellSymbols = array(
		"\xda", "\xdc", "\xbf",
		"\xb3",
		"\xc0", "\xc4", "\xd9",
	);
	private static $_htmlSymbols     = array(
		"&#9484;", "&#9604;", "&#9488;",
		"&#9474;",
		"&#9492;", "&#9472;", "&#9496;",
	);

	public static function decorate( kintVariableData $kintVar, $level = 0 )
	{
		$output = '';
		if ( $level === 0 ) {
			$name          = $kintVar->name ? $kintVar->name : 'literal';
			$kintVar->name = null;

			$output .= self::_title( $name );
		}


		$space = str_repeat( $s = '    ', $level );
		$output .= $space . self::_drawHeader( $kintVar );


		if ( $kintVar->extendedValue !== null ) {
			$output .= ' ' . ( $kintVar->type === 'array' ? '[' : '(' ) . PHP_EOL;


			if ( is_array( $kintVar->extendedValue ) ) {
				foreach ( $kintVar->extendedValue as $v ) {
					$output .= self::decorate( $v, $level + 1 );
				}
			} elseif ( is_string( $kintVar->extendedValue ) ) {
				$output .= $space . $s . $kintVar->extendedValue . PHP_EOL; # "depth too great" or similar
			} else {
				$output .= self::decorate( $kintVar->extendedValue, $level + 1 ); //it's kintVariableData
			}
			$output .= $space . ( $kintVar->type === 'array' ? ']' : ')' ) . PHP_EOL;
		} else {
			$output .= PHP_EOL;
		}

		return $output;
	}

	public static function decorateTrace( $traceData )
	{
		$output   = self::_title( 'TRACE' );
		$lastStep = count( $traceData );
		foreach ( $traceData as $stepNo => $step ) {
			$title = str_pad( ++$stepNo . ': ', 4, ' ' );

			$title .= self::_colorize(
				( isset( $step['file'] ) ? self::_buildCalleeString( $step ) : 'PHP internal call' ),
				'title'
			);

			if ( !empty( $step['function'] ) ) {
				$title .= '    ' . $step['function'];
				if ( isset( $step['args'] ) ) {
					$title .= '(';
					if ( empty( $step['args'] ) ) {
						$title .= ')';
					} else {
					}
					$title .= PHP_EOL;
				}
			}

			$output .= $title;

			if ( !empty( $step['args'] ) ) {
				$appendDollar = $step['function'] === '{closure}' ? '' : '$';

				$i = 0;
				foreach ( $step['args'] as $name => $argument ) {
					$argument           = kintParser::factory(
						$argument,
						$name ? $appendDollar . $name : '#' . ++$i
					);
					$argument->operator = $name ? ' =' : ':';
					$maxLevels          = Kint::$maxLevels;
					if ( $maxLevels ) {
						Kint::$maxLevels = $maxLevels + 2;
					}
					$output .= self::decorate( $argument, 2 );
					if ( $maxLevels ) {
						Kint::$maxLevels = $maxLevels;
					}
				}
				$output .= '    )' . PHP_EOL;
			}

			if ( !empty( $step['object'] ) ) {
				$output .= self::_colorize(
					'    ' . self::_char( '─', 27 ) . ' Callee object ' . self::_char( '─', 34 ),
					'title'
				);

				$maxLevels = Kint::$maxLevels;
				if ( $maxLevels ) {
					# in cli the terminal window is filled too quickly to display huge objects
					Kint::$maxLevels = Kint::enabled() === Kint::MODE_CLI
						? 1
						: $maxLevels + 1;
				}
				$output .= self::decorate( kintParser::factory( $step['object'] ), 1 );
				if ( $maxLevels ) {
					Kint::$maxLevels = $maxLevels;
				}
			}

			if ( $stepNo !== $lastStep ) {
				$output .= self::_colorize( self::_char( '─', 80 ), 'title' );
			}
		}

		return $output;
	}


	private static function _colorize( $text, $type, $nlAfter = true )
	{
		$nlAfter = $nlAfter ? PHP_EOL : '';

		switch ( Kint::enabled() ) {
			case Kint::MODE_PLAIN:
				if ( !self::$_enableColors ) return $text . $nlAfter;

				switch ( $type ) {
					case 'value':
						$text = "<i>{$text}</i>";
						break;
					case 'type':
						$text = "<b>{$text}</b>";
						break;
					case 'title':
						$text = "<u>{$text}</u>";
						break;
				}

				return $text . $nlAfter;
				break;
			case Kint::MODE_CLI:
				if ( !self::$_enableColors ) return $text . $nlAfter;

				$optionsMap = array(
					'title' => "\x1b[36m", # cyan
					'type'  => "\x1b[35;1m", # magenta bold
					'value' => "\x1b[32m", # green
				);

				return $optionsMap[ $type ] . $text . "\x1b[0m" . $nlAfter;
				break;
			case Kint::MODE_WHITESPACE:
			default:
				return $text . $nlAfter;
				break;
		}
	}


	private static function _char( $char, $repeat = null )
	{
		switch ( Kint::enabled() ) {
			case Kint::MODE_PLAIN:
				$char = self::$_htmlSymbols[ array_search( $char, self::$_utfSymbols, true ) ];
				break;
			case Kint::MODE_CLI:
				$inWindowsShell = PHP_SAPI === 'cli' && DIRECTORY_SEPARATOR !== '/';
				if ( $inWindowsShell ) {
					$char = self::$_winShellSymbols[ array_search( $char, self::$_utfSymbols, true ) ];
				}
				break;
			case Kint::MODE_WHITESPACE:
			default:
				break;
		}

		return $repeat ? str_repeat( $char, $repeat ) : $char;
	}

	private static function _title( $text )
	{
		$escaped          = kintParser::escape( $text );
		$lengthDifference = strlen( $escaped ) - strlen( $text );
		return
			self::_colorize(
				self::_char( '┌' ) . self::_char( '─', 78 ) . self::_char( '┐' ) . PHP_EOL
				. self::_char( '│' ),
				'title',
				false
			)

			. self::_colorize( str_pad( $escaped, 78 + $lengthDifference, ' ', STR_PAD_BOTH ), 'title', false )

			. self::_colorize( self::_char( '│' ) . PHP_EOL
				. self::_char( '└' ) . self::_char( '─', 78 ) . self::_char( '┘' ),
				'title'
			);
	}

	public static function wrapStart()
	{
		if ( Kint::enabled() === Kint::MODE_PLAIN ) {
			return '<pre class="-kint">';
		}
		return '';
	}

	public static function wrapEnd( $callee, $miniTrace, $prevCaller )
	{
		$lastLine = self::_colorize( self::_char( "═", 80 ), 'title' );
		$lastChar = Kint::enabled() === Kint::MODE_PLAIN ? '</pre>' : '';


		if ( !Kint::$displayCalledFrom ) return $lastLine . $lastChar;


		return $lastLine . self::_colorize( 'Called from ' . self::_buildCalleeString( $callee ), 'title' ) . $lastChar;
	}


	private static function _drawHeader( kintVariableData $kintVar )
	{
		$output = '';

		if ( $kintVar->access ) {
			$output .= ' ' . $kintVar->access;
		}

		if ( $kintVar->name !== null && $kintVar->name !== '' ) {
			$output .= ' ' . kintParser::escape( $kintVar->name );
		}

		if ( $kintVar->operator ) {
			$output .= ' ' . $kintVar->operator;
		}

		$output .= ' ' . self::_colorize( $kintVar->type, 'type', false );

		if ( $kintVar->size !== null ) {
			$output .= ' (' . $kintVar->size . ')';
		}


		if ( $kintVar->value !== null && $kintVar->value !== '' ) {
			$output .= ' ' . self::_colorize(
					$kintVar->value, # escape shell
					'value',
					false
				);
		}

		return ltrim( $output );
	}

	private static function _buildCalleeString( $callee )
	{
		if ( Kint::enabled() === Kint::MODE_CLI ) { // todo win/nix
			return "{$callee['file']}:{$callee['line']}";
		}

		$url           = Kint::getIdeLink( $callee['file'], $callee['line'] );
		$shortenedName = Kint::shortenPath( $callee['file'] ) . ':' . $callee['line'];

		if ( Kint::enabled() === Kint::MODE_PLAIN ) {
			if ( strpos( $url, 'http://' ) === 0 ) {
				$calleeInfo = "<a href=\"#\"onclick=\""
					. "X=new XMLHttpRequest;"
					. "X.open('GET','{$url}');"
					. "X.send();"
					. "return!1\">{$shortenedName}</a>";
			} else {
				$calleeInfo = "<a href=\"{$url}\">{$shortenedName}</a>";
			}
		} else {
			$calleeInfo = $shortenedName;
		}

		return $calleeInfo;
	}

	public static function init()
	{
		self::$_enableColors =
			Kint::$cliColors
			&& ( DIRECTORY_SEPARATOR === '/' || getenv( 'ANSICON' ) !== false || getenv( 'ConEmuANSI' ) === 'ON' );

		return Kint::enabled() === Kint::MODE_PLAIN
			? '<style>.-kint i{color:#d00;font-style:normal}.-kint u{color:#030;text-decoration:none;font-weight:bold}</style>'
			: '';
	}
}