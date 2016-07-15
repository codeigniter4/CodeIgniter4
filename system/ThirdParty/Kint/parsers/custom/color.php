<?php
class Kint_Parsers_Color extends kintParser
{
	private static $_css3Named = array(
		'aliceblue'=>'#f0f8ff','antiquewhite'=>'#faebd7','aqua'=>'#00ffff','aquamarine'=>'#7fffd4','azure'=>'#f0ffff',
		'beige'=>'#f5f5dc','bisque'=>'#ffe4c4','black'=>'#000000','blanchedalmond'=>'#ffebcd','blue'=>'#0000ff',
		'blueviolet'=>'#8a2be2','brown'=>'#a52a2a','burlywood'=>'#deb887','cadetblue'=>'#5f9ea0','chartreuse'=>'#7fff00',
		'chocolate'=>'#d2691e','coral'=>'#ff7f50','cornflowerblue'=>'#6495ed','cornsilk'=>'#fff8dc','crimson'=>'#dc143c',
		'cyan'=>'#00ffff','darkblue'=>'#00008b','darkcyan'=>'#008b8b','darkgoldenrod'=>'#b8860b','darkgray'=>'#a9a9a9',
		'darkgrey'=>'#a9a9a9','darkgreen'=>'#006400','darkkhaki'=>'#bdb76b','darkmagenta'=>'#8b008b',
		'darkolivegreen'=>'#556b2f','darkorange'=>'#ff8c00','darkorchid'=>'#9932cc','darkred'=>'#8b0000',
		'darksalmon'=>'#e9967a','darkseagreen'=>'#8fbc8f','darkslateblue'=>'#483d8b','darkslategray'=>'#2f4f4f',
		'darkslategrey'=>'#2f4f4f','darkturquoise'=>'#00ced1','darkviolet'=>'#9400d3','deeppink'=>'#ff1493',
		'deepskyblue'=>'#00bfff','dimgray'=>'#696969','dimgrey'=>'#696969','dodgerblue'=>'#1e90ff',
		'firebrick'=>'#b22222','floralwhite'=>'#fffaf0','forestgreen'=>'#228b22','fuchsia'=>'#ff00ff',
		'gainsboro'=>'#dcdcdc','ghostwhite'=>'#f8f8ff','gold'=>'#ffd700','goldenrod'=>'#daa520','gray'=>'#808080',
		'grey'=>'#808080','green'=>'#008000','greenyellow'=>'#adff2f','honeydew'=>'#f0fff0','hotpink'=>'#ff69b4',
		'indianred'=>'#cd5c5c','indigo'=>'#4b0082','ivory'=>'#fffff0','khaki'=>'#f0e68c','lavender'=>'#e6e6fa',
		'lavenderblush'=>'#fff0f5','lawngreen'=>'#7cfc00','lemonchiffon'=>'#fffacd','lightblue'=>'#add8e6',
		'lightcoral'=>'#f08080','lightcyan'=>'#e0ffff','lightgoldenrodyellow'=>'#fafad2','lightgray'=>'#d3d3d3',
		'lightgrey'=>'#d3d3d3','lightgreen'=>'#90ee90','lightpink'=>'#ffb6c1','lightsalmon'=>'#ffa07a',
		'lightseagreen'=>'#20b2aa','lightskyblue'=>'#87cefa','lightslategray'=>'#778899','lightslategrey'=>'#778899',
		'lightsteelblue'=>'#b0c4de','lightyellow'=>'#ffffe0','lime'=>'#00ff00','limegreen'=>'#32cd32','linen'=>'#faf0e6',
		'magenta'=>'#ff00ff','maroon'=>'#800000','mediumaquamarine'=>'#66cdaa','mediumblue'=>'#0000cd',
		'mediumorchid'=>'#ba55d3','mediumpurple'=>'#9370d8','mediumseagreen'=>'#3cb371','mediumslateblue'=>'#7b68ee',
		'mediumspringgreen'=>'#00fa9a','mediumturquoise'=>'#48d1cc','mediumvioletred'=>'#c71585',
		'midnightblue'=>'#191970','mintcream'=>'#f5fffa','mistyrose'=>'#ffe4e1','moccasin'=>'#ffe4b5',
		'navajowhite'=>'#ffdead','navy'=>'#000080','oldlace'=>'#fdf5e6','olive'=>'#808000','olivedrab'=>'#6b8e23',
		'orange'=>'#ffa500','orangered'=>'#ff4500','orchid'=>'#da70d6','palegoldenrod'=>'#eee8aa','palegreen'=>'#98fb98',
		'paleturquoise'=>'#afeeee','palevioletred'=>'#d87093','papayawhip'=>'#ffefd5','peachpuff'=>'#ffdab9',
		'peru'=>'#cd853f','pink'=>'#ffc0cb','plum'=>'#dda0dd','powderblue'=>'#b0e0e6','purple'=>'#800080',
		'red'=>'#ff0000','rosybrown'=>'#bc8f8f','royalblue'=>'#4169e1','saddlebrown'=>'#8b4513','salmon'=>'#fa8072',
		'sandybrown'=>'#f4a460','seagreen'=>'#2e8b57','seashell'=>'#fff5ee','sienna'=>'#a0522d','silver'=>'#c0c0c0',
		'skyblue'=>'#87ceeb','slateblue'=>'#6a5acd','slategray'=>'#708090','slategrey'=>'#708090','snow'=>'#fffafa',
		'springgreen'=>'#00ff7f','steelblue'=>'#4682b4','tan'=>'#d2b48c','teal'=>'#008080','thistle'=>'#d8bfd8',
		'tomato'=>'#ff6347','turquoise'=>'#40e0d0','violet'=>'#ee82ee','wheat'=>'#f5deb3','white'=>'#ffffff',
		'whitesmoke'=>'#f5f5f5','yellow'=>'#ffff00','yellowgreen'=>'#9acd32'
	);


	protected function _parse( & $variable )
	{
		if ( !self::_fits( $variable ) ) return false;

		$this->type  = 'CSS color';
		$variants    = self::_convert( $variable );
		$this->value =
			"<div style=\"background:{$variable}\" class=\"kint-color-preview\">{$variable}</div>"
			. "<strong>hex :</strong> {$variants['hex']}\n"
			. "<strong>rgb :</strong> {$variants['rgb']}\n"
			. ( isset( $variants['name'] ) ? "<strong>name:</strong> {$variants['name']}\n" : '' )
			. "<strong>hsl :</strong> {$variants['hsl']}";
	}


	private static function _fits( $variable )
	{
		if ( !is_string( $variable ) ) return false;

		$var = strtolower( trim( $variable ) );

		return isset( self::$_css3Named[$var] )
			|| preg_match(
				'/^(?:#[0-9A-Fa-f]{3}|#[0-9A-Fa-f]{6}|(?:rgb|hsl)a?\s*\((?:\s*[0-9.%]+\s*,?){3,4}\))$/',
				$var
			);
	}

	private static function _convert( $color )
	{
		$color         = strtolower( $color );
		$decimalColors = array();
		$variants      = array(
			'hex'  => null,
			'rgb'  => null,
			'name' => null,
			'hsl'  => null,
		);

		if ( isset( self::$_css3Named[ $color ] ) ) {
			$variants['name'] = $color;
			$color            = self::$_css3Named[ $color ];
		}

		if ( $color{0} === '#' ) {
			$variants['hex'] = $color;
			$color           = substr( $color, 1 );
			if ( strlen( $color ) === 6 ) {
				$colors = str_split( $color, 2 );
			} else {
				$colors = array(
					$color{0} . $color{0},
					$color{1} . $color{1},
					$color{2} . $color{2},
				);
			}

			$decimalColors = array_map( 'hexdec', $colors );
		} elseif ( substr( $color, 0, 3 ) === 'rgb' ) {
			$variants['rgb'] = $color;
			preg_match_all( '#([0-9.%]+)#', $color, $matches );
			$decimalColors = $matches[1];
			foreach ( $decimalColors as &$color ) {
				if ( strpos( $color, '%' ) !== false ) {
					$color = str_replace( '%', '', $color ) * 2.55;
				}
			}


		} elseif ( substr( $color, 0, 3 ) === 'hsl' ) {
			$variants['hsl'] = $color;
			preg_match_all( '#([0-9.%]+)#', $color, $matches );

			$colors = $matches[1];
			$colors[0] /= 360;
			$colors[1] = str_replace( '%', '', $colors[1] ) / 100;
			$colors[2] = str_replace( '%', '', $colors[2] ) / 100;

			$decimalColors = self::_HSLtoRGB( $colors );
			if ( isset( $colors[3] ) ) {
				$decimalColors[] = $colors[3];
			}
		}

		if ( isset( $decimalColors[3] ) ) {
			$alpha = $decimalColors[3];
			unset( $decimalColors[3] );
		} else {
			$alpha = null;
		}
		foreach ( $variants as $type => &$variant ) {
			if ( isset( $variant ) ) continue;

			switch ( $type ) {
				case 'hex':
					$variant = '#';
					foreach ( $decimalColors as &$color ) {
						$variant .= str_pad( dechex( $color ), 2, "0", STR_PAD_LEFT );
					}
					$variant .= isset( $alpha ) ? ' (alpha omitted)' : '';
					break;
				case 'rgb':
					$rgb = $decimalColors;
					if ( isset( $alpha ) ) {
						$rgb[] = $alpha;
						$a     = 'a';
					} else {
						$a = '';
					}
					$variant = "rgb{$a}( " . implode( ', ', $rgb ) . " )";
					break;
				case 'hsl':
					$rgb = self::_RGBtoHSL( $decimalColors );
					if ( $rgb === null ) {
						unset( $variants[ $type ] );
						break;
					}
					if ( isset( $alpha ) ) {
						$rgb[] = $alpha;
						$a     = 'a';
					} else {
						$a = '';
					}

					$variant = "hsl{$a}( " . implode( ', ', $rgb ) . " )";
					break;
				case 'name':
					// [!] name in initial variants array must go after hex
					if ( ( $key = array_search( $variants['hex'], self::$_css3Named, true ) ) !== false ) {
						$variant = $key;
					} else {
						unset( $variants[ $type ] );
					}
					break;
			}

		}

		return $variants;
	}


	private static function _HSLtoRGB( array $hsl )
	{
		list( $h, $s, $l ) = $hsl;
		$m2 = ( $l <= 0.5 ) ? $l * ( $s + 1 ) : $l + $s - $l * $s;
		$m1 = $l * 2 - $m2;
		return array(
			round( self::_hue2rgb( $m1, $m2, $h + 0.33333 ) * 255 ),
			round( self::_hue2rgb( $m1, $m2, $h ) * 255 ),
			round( self::_hue2rgb( $m1, $m2, $h - 0.33333 ) * 255 ),
		);
	}


	/**
	 * Helper function for _color_hsl2rgb().
	 */
	private static function _hue2rgb( $m1, $m2, $h )
	{
		$h = ( $h < 0 ) ? $h + 1 : ( ( $h > 1 ) ? $h - 1 : $h );
		if ( $h * 6 < 1 ) return $m1 + ( $m2 - $m1 ) * $h * 6;
		if ( $h * 2 < 1 ) return $m2;
		if ( $h * 3 < 2 ) return $m1 + ( $m2 - $m1 ) * ( 0.66666 - $h ) * 6;
		return $m1;
	}


	private static function _RGBtoHSL( array $rgb )
	{
		list( $clrR, $clrG, $clrB ) = $rgb;

		$clrMin   = min( $clrR, $clrG, $clrB );
		$clrMax   = max( $clrR, $clrG, $clrB );
		$deltaMax = $clrMax - $clrMin;

		$L = ( $clrMax + $clrMin ) / 510;

		if ( 0 == $deltaMax ) {
			$H = 0;
			$S = 0;
		} else {
			if ( 0.5 > $L ) {
				$S = $deltaMax / ( $clrMax + $clrMin );
			} else {
				$S = $deltaMax / ( 510 - $clrMax - $clrMin );
			}

			if ( $clrMax == $clrR ) {
				$H = ( $clrG - $clrB ) / ( 6.0 * $deltaMax );
			} else if ( $clrMax == $clrG ) {
				$H = 1 / 3 + ( $clrB - $clrR ) / ( 6.0 * $deltaMax );
			} else {
				$H = 2 / 3 + ( $clrR - $clrG ) / ( 6.0 * $deltaMax );
			}

			if ( 0 > $H ) $H += 1;
			if ( 1 < $H ) $H -= 1;
		}
		return array(
			round( $H * 360 ),
			round( $S * 100 ) . '%',
			round( $L * 100 ) . '%'
		);

	}
}

/* *************
 * TEST DATA
 *
dd(array(
'hsl(0,  100%,50%)',
'hsl(30, 100%,50%)',
'hsl(60, 100%,50%)',
'hsl(90, 100%,50%)',
'hsl(120,100%,50%)',
'hsl(150,100%,50%)',
'hsl(180,100%,50%)',
'hsl(210,100%,50%)',
'hsl(240,100%,50%)',
'hsl(270,100%,50%)',
'hsl(300,100%,50%)',
'hsl(330,100%,50%)',
'hsl(360,100%,50%)',
'hsl(120,100%,25%)',
'hsl(120,100%,50%)',
'hsl(120,100%,75%)',
'hsl(120,100%,50%)',
'hsl(120, 67%,50%)',
'hsl(120, 33%,50%)',
'hsl(120,  0%,50%)',
'hsl(120, 60%,70%)',
'#f03',
'#F03',
'#ff0033',
'#FF0033',
'rgb(255,0,51)',
'rgb(255, 0, 51)',
'rgb(100%,0%,20%)',
'rgb(100%, 0%, 20%)',
'hsla(240,100%,50%,0.05)',
'hsla(240,100%,50%, 0.4)',
'hsla(240,100%,50%, 0.7)',
'hsla(240,100%,50%,   1)',
'rgba(255,0,0,0.1)',
'rgba(255,0,0,0.4)',
'rgba(255,0,0,0.7)',
'rgba(255,0,0,  1)',
'black',
'silver',
'gray',
'white',
'maroon',
'red',
'purple',
'fuchsia',
'green',
'lime',
'olive',
'yellow',
'navy',
'blue',
'teal',
'aqua',
'orange',
'aliceblue',
'antiquewhite',
'aquamarine',
'azure',
'beige',
'bisque',
'blanchedalmond',
'blueviolet',
'brown',
'burlywood',
'cadetblue',
'chartreuse',
'chocolate',
'coral',
'cornflowerblue',
'cornsilk',
'crimson',
'darkblue',
'darkcyan',
'darkgoldenrod',
'darkgray',
'darkgreen',
'darkgrey',
'darkkhaki',
'darkmagenta',
'darkolivegreen',
'darkorange',
'darkorchid',
'darkred',
'darksalmon',
'darkseagreen',
'darkslateblue',
'darkslategray',
'darkslategrey',
'darkturquoise',
'darkviolet',
'deeppink',
'deepskyblue',
'dimgray',
'dimgrey',
'dodgerblue',
'firebrick',
'floralwhite',
'forestgreen',
'gainsboro',
'ghostwhite',
'gold',
'goldenrod',
'greenyellow',
'grey',
'honeydew',
'hotpink',
'indianred',
'indigo',
'ivory',
'khaki',
'lavender',
'lavenderblush',
'lawngreen',
'lemonchiffon',
'lightblue',
'lightcoral',
'lightcyan',
'lightgoldenrodyellow',
'lightgray',
'lightgreen',
'lightgrey',
'lightpink',
'lightsalmon',
'lightseagreen',
'lightskyblue',
'lightslategray',
'lightslategrey',
'lightsteelblue',
'lightyellow',
'limegreen',
'linen',
'mediumaquamarine',
'mediumblue',
'mediumorchid',
'mediumpurple',
'mediumseagreen',
'mediumslateblue',
'mediumspringgreen',
'mediumturquoise',
'mediumvioletred',
'midnightblue',
'mintcream',
'mistyrose',
'moccasin',
'navajowhite',
'oldlace',
'olivedrab',
));*/