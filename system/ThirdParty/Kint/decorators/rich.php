<?php

class Kint_Decorators_Rich
{
	# make calls to Kint::dump() from different places in source coloured differently.
	private static $_usedColors = array();

	public static function decorate( kintVariableData $kintVar )
	{
		$output = '<dl>';

		$extendedPresent = $kintVar->extendedValue !== null || $kintVar->_alternatives !== null;

		if ( $extendedPresent ) {
			$class = 'kint-parent';
			if ( Kint::$expandedByDefault ) {
				$class .= ' kint-show';
			}
			$output .= '<dt class="' . $class . '">';
		} else {
			$output .= '<dt>';
		}

		if ( $extendedPresent ) {
			$output .= '<span class="kint-popup-trigger" title="Open in new window">&rarr;</span><nav></nav>';
		}

		$output .= self::_drawHeader( $kintVar ) . $kintVar->value . '</dt>';


		if ( $extendedPresent ) {
			$output .= '<dd>';
		}

		if ( isset( $kintVar->extendedValue ) ) {

			if ( is_array( $kintVar->extendedValue ) ) {
				foreach ( $kintVar->extendedValue as $v ) {
					$output .= self::decorate( $v );
				}
			} elseif ( is_string( $kintVar->extendedValue ) ) {
				$output .= '<pre>' . $kintVar->extendedValue . '</pre>';
			} else {
				$output .= self::decorate( $kintVar->extendedValue ); //it's kint's container
			}

		} elseif ( isset( $kintVar->_alternatives ) ) {
			$output .= "<ul class=\"kint-tabs\">";

			foreach ( $kintVar->_alternatives as $k => $var ) {
				$active = $k === 0 ? ' class="kint-active-tab"' : '';
				$output .= "<li{$active}>" . self::_drawHeader( $var, false ) . '</li>';
			}

			$output .= "</ul><ul>";

			foreach ( $kintVar->_alternatives as $var ) {
				$output .= "<li>";

				$var = $var->value;

				if ( is_array( $var ) ) {
					foreach ( $var as $v ) {
						$output .= is_string( $v )
							? '<pre>' . $v . '</pre>'
							: self::decorate( $v );
					}
				} elseif ( is_string( $var ) ) {
					$output .= '<pre>' . $var . '</pre>';
				} elseif ( isset( $var ) ) {
					throw new Exception(
						'Kint has encountered an error, '
						. 'please paste this report to https://github.com/raveren/kint/issues<br>'
						. 'Error encountered at ' . basename( __FILE__ ) . ':' . __LINE__ . '<br>'
						. ' variables: '
						. htmlspecialchars( var_export( $kintVar->_alternatives, true ), ENT_QUOTES )
					);
				}

				$output .= "</li>";
			}

			$output .= "</ul>";
		}
		if ( $extendedPresent ) {
			$output .= '</dd>';
		}

		$output .= '</dl>';

		return $output;
	}

	public static function decorateTrace( $traceData )
	{
		$output = '<dl class="kint-trace">';

		foreach ( $traceData as $i => $step ) {
			$class = 'kint-parent';
			if ( Kint::$expandedByDefault ) {
				$class .= ' kint-show';
			}

			$output .= '<dt class="' . $class . '">'
				. '<b>' . ( $i + 1 ) . '</b> '
				. '<nav></nav>'
				. '<var>';

			if ( isset( $step['file'] ) ) {
				$output .= self::_ideLink( $step['file'], $step['line'] );
			} else {
				$output .= 'PHP internal call';
			}

			$output .= '</var>';

			$output .= $step['function'];

			if ( isset( $step['args'] ) ) {
				$output .= '(' . implode( ', ', array_keys( $step['args'] ) ) . ')';
			}
			$output .= '</dt><dd>';
			$firstTab = ' class="kint-active-tab"';
			$output .= '<ul class="kint-tabs">';

			if ( !empty( $step['source'] ) ) {
				$output .= "<li{$firstTab}>Source</li>";
				$firstTab = '';
			}

			if ( !empty( $step['args'] ) ) {
				$output .= "<li{$firstTab}>Arguments</li>";
				$firstTab = '';
			}

			if ( !empty( $step['object'] ) ) {
				kintParser::reset();
				$calleeDump = kintParser::factory( $step['object'] );

				$output .= "<li{$firstTab}>Callee object [{$calleeDump->type}]</li>";
			}


			$output .= '</ul><ul>';


			if ( !empty( $step['source'] ) ) {
				$output .= "<li><pre class=\"kint-source\">{$step['source']}</pre></li>";
			}

			if ( !empty( $step['args'] ) ) {
				$output .= "<li>";
				foreach ( $step['args'] as $k => $arg ) {
					kintParser::reset();
					$output .= self::decorate( kintParser::factory( $arg, $k ) );
				}
				$output .= "</li>";
			}
			if ( !empty( $step['object'] ) ) {
				$output .= "<li>" . self::decorate( $calleeDump ) . "</li>";
			}

			$output .= '</ul></dd>';
		}
		$output .= '</dl>';

		return $output;
	}


	/**
	 * called for each dump, opens the html tag
	 *
	 * @param array $callee caller information taken from debug backtrace
	 *
	 * @return string
	 */
	public static function wrapStart()
	{
		return "<div class=\"kint\">";
	}


	/**
	 * closes Kint::_wrapStart() started html tags and displays callee information
	 *
	 * @param array $callee caller information taken from debug backtrace
	 * @param array $miniTrace full path to kint call
	 * @param array $prevCaller previous caller information taken from debug backtrace
	 *
	 * @return string
	 */
	public static function wrapEnd( $callee, $miniTrace, $prevCaller )
	{
		if ( !Kint::$displayCalledFrom ) {
			return '</div>';
		}

		$callingFunction = '';
		$calleeInfo      = '';
		$traceDisplay    = '';
		if ( isset( $prevCaller['class'] ) ) {
			$callingFunction = $prevCaller['class'];
		}
		if ( isset( $prevCaller['type'] ) ) {
			$callingFunction .= $prevCaller['type'];
		}
		if ( isset( $prevCaller['function'] )
			&& !in_array( $prevCaller['function'], array( 'include', 'include_once', 'require', 'require_once' ) )
		) {
			$callingFunction .= $prevCaller['function'] . '()';
		}
		$callingFunction and $callingFunction = " [{$callingFunction}]";


		if ( isset( $callee['file'] ) ) {
			$calleeInfo .= 'Called from ' . self::_ideLink( $callee['file'], $callee['line'] );
		}

		if ( !empty( $miniTrace ) ) {
			$traceDisplay = '<ol>';
			foreach ( $miniTrace as $step ) {
				$traceDisplay .= '<li>' . self::_ideLink( $step['file'], $step['line'] ); // closing tag not required
				if ( isset( $step['function'] )
					&& !in_array( $step['function'], array( 'include', 'include_once', 'require', 'require_once' ) )
				) {
					$classString = ' [';
					if ( isset( $step['class'] ) ) {
						$classString .= $step['class'];
					}
					if ( isset( $step['type'] ) ) {
						$classString .= $step['type'];
					}
					$classString .= $step['function'] . '()]';
					$traceDisplay .= $classString;
				}
			}
			$traceDisplay .= '</ol>';

			$calleeInfo = '<nav></nav>' . $calleeInfo;
		}


		return "<footer>"
		. '<span class="kint-popup-trigger" title="Open in new window">&rarr;</span> '
		. "{$calleeInfo}{$callingFunction}{$traceDisplay}"
		. "</footer></div>";
	}


	private static function _drawHeader( kintVariableData $kintVar, $verbose = true )
	{
		$output = '';
		if ( $verbose ) {
			if ( $kintVar->access !== null ) {
				$output .= "<var>" . $kintVar->access . "</var> ";
			}

			if ( $kintVar->name !== null && $kintVar->name !== '' ) {
				$output .= "<dfn>" . kintParser::escape( $kintVar->name ) . "</dfn> ";
			}

			if ( $kintVar->operator !== null ) {
				$output .= $kintVar->operator . " ";
			}
		}

		if ( $kintVar->type !== null ) {
			if ( $verbose ) {
				$output .= "<var>";
			}

			$output .= $kintVar->type;

			if ( $verbose ) {
				$output .= "</var>";
			} else {
				$output .= " ";
			}
		}


		if ( $kintVar->size !== null ) {
			$output .= "(" . $kintVar->size . ") ";
		}

		return $output;
	}

	private static function _ideLink( $file, $line )
	{
		$shortenedPath = Kint::shortenPath( $file );
		if ( !Kint::$fileLinkFormat ) return $shortenedPath . ':' . $line;

		$ideLink = Kint::getIdeLink( $file, $line );
		$class   = ( strpos( $ideLink, 'http://' ) === 0 ) ? 'class="kint-ide-link" ' : '';
		return "<a {$class}href=\"{$ideLink}\">{$shortenedPath}:{$line}</a>";
	}


	/**
	 * produces css and js required for display. May be called multiple times, will only produce output once per
	 * pageload or until `-` or `@` modifier is used
	 *
	 * @return string
	 */
	public static function init()
	{
		$baseDir = KINT_DIR . 'view/compiled/';

		if ( !is_readable( $cssFile = $baseDir . Kint::$theme . '.css' ) ) {
			$cssFile = $baseDir . 'original.css';
		}

		return
			'<script class="-kint-js">' . file_get_contents( $baseDir . 'kint.js' ) . '</script>'
			. '<style class="-kint-css">' . file_get_contents( $cssFile ) . "</style>\n";
	}
}