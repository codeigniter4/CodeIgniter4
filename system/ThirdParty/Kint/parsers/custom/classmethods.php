<?php

class Kint_Parsers_ClassMethods extends kintParser
{
	private static $cache = array();

	protected function _parse( &$variable )
	{
		if ( !KINT_PHP53 || !is_object( $variable ) ) return false;

		$className = get_class( $variable );

		# assuming class definition will not change inside one request
		if ( !isset( self::$cache[ $className ] ) ) {
			$reflection = new ReflectionClass( $variable );

			$public = $private = $protected = array();

			// Class methods
			foreach ( $reflection->getMethods() as $method ) {
				$params = array();

				// Access type
				$access = implode( ' ', Reflection::getModifierNames( $method->getModifiers() ) );

				// Method parameters
				foreach ( $method->getParameters() as $param ) {
					$paramString = '';

					if ( $param->isArray() ) {
						$paramString .= 'array ';
					} else {
						try {
							if ( $paramClassName = $param->getClass() ) {
								$paramString .= $paramClassName->name . ' ';
							}
						} catch ( ReflectionException $e ) {
							preg_match( '/\[\s\<\w+?>\s([\w]+)/s', $param->__toString(), $matches );
							$paramClassName = isset( $matches[1] ) ? $matches[1] : '';

							$paramString .= ' UNDEFINED CLASS (' . $paramClassName . ') ';
						}
					}

					$paramString .= ( $param->isPassedByReference() ? '&' : '' ) . '$' . $param->getName();

					if ( $param->isDefaultValueAvailable() ) {
						if ( is_array( $param->getDefaultValue() ) ) {
							$arrayValues = array();
							foreach ( $param->getDefaultValue() as $key => $value ) {
								$arrayValues[] = $key . ' => ' . $value;
							}

							$defaultValue = 'array(' . implode( ', ', $arrayValues ) . ')';
						} elseif ( $param->getDefaultValue() === null ) {
							$defaultValue = 'NULL';
						} elseif ( $param->getDefaultValue() === false ) {
							$defaultValue = 'false';
						} elseif ( $param->getDefaultValue() === true ) {
							$defaultValue = 'true';
						} elseif ( $param->getDefaultValue() === '' ) {
							$defaultValue = '""';
						} else {
							$defaultValue = $param->getDefaultValue();
						}

						$paramString .= ' = ' . $defaultValue;
					}

					$params[] = $paramString;
				}

				$output = new kintVariableData;

				// Simple DocBlock parser, look for @return
				if ( ( $docBlock = $method->getDocComment() ) ) {
					$matches = array();
					if ( preg_match_all( '/@(\w+)\s+(.*)\r?\n/m', $docBlock, $matches ) ) {
						$lines = array_combine( $matches[1], $matches[2] );
						if ( isset( $lines['return'] ) ) {
							$output->operator = '->';
							# since we're outputting code, assumption that the string is utf8 is most likely correct
							# and saves resources
							$output->type = self::escape( $lines['return'], 'UTF-8' );
						}
					}
				}

				$output->name   = ( $method->returnsReference() ? '&' : '' ) . $method->getName() . '('
					. implode( ', ', $params ) . ')';
				$output->access = $access;

				if ( is_string( $docBlock ) ) {
					$lines = array();
					foreach ( explode( "\n", $docBlock ) as $line ) {
						$line = trim( $line );

						if ( in_array( $line, array( '/**', '/*', '*/' ) ) ) {
							continue;
						} elseif ( strpos( $line, '*' ) === 0 ) {
							$line = substr( $line, 1 );
						}

						$lines[] = self::escape( trim( $line ), 'UTF-8' );
					}

					$output->extendedValue = implode( "\n", $lines ) . "\n\n";
				}

				$declaringClass     = $method->getDeclaringClass();
				$declaringClassName = $declaringClass->getName();

				if ( $declaringClassName !== $className ) {
					$output->extendedValue .= "<small>Inherited from <i>{$declaringClassName}</i></small>\n";
				}

				$fileName = Kint::shortenPath( $method->getFileName() ) . ':' . $method->getStartLine();
				$output->extendedValue .= "<small>Defined in {$fileName}</small>";

				$sortName = $access . $method->getName();

				if ( $method->isPrivate() ) {
					$private[ $sortName ] = $output;
				} elseif ( $method->isProtected() ) {
					$protected[ $sortName ] = $output;
				} else {
					$public[ $sortName ] = $output;
				}
			}

			if ( !$private && !$protected && !$public ) {
				self::$cache[ $className ] = false;
			}

			ksort( $public );
			ksort( $protected );
			ksort( $private );

			self::$cache[ $className ] = $public + $protected + $private;
		}

		if ( count( self::$cache[ $className ] ) === 0 ) {
			return false;
		}

		$this->value = self::$cache[ $className ];
		$this->type  = 'Available methods';
		$this->size  = count( self::$cache[ $className ] );
	}
}