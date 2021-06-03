<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Mailer;

use Config\Mailer;

/**
 * Mailer Encode Class
 *
 * Handles the encoding of various portions of
 * an email into all their possible forms.
 *
 * @property-read string $charset
 * @property-read string $encoding
 * @property-read string $newline
 * @property-read string $crlf
 */
final class Encode
{
	/**
	 * Valid newline characters.
	 */
	private const NEWLINES = [
		"\r\n",
		"\r",
		"\n",
	];

	/**
	 * Valid mail encodings
	 */
	private const ENCODINGS = [
		'7bit',
		'8bit',
	];

	/**
	 * Character sets valid for 7-bit encoding, excluding language suffix.
	 */
	private const CHARSETS = [
		'us-ascii',
		'iso-2022-',
	];

	/**
	 * ASCII code numbers for "safe" characters that can always be used literally,
	 * without encoding, as described in RFC 2049.
	 *
	 * @link http://www.ietf.org/rfc/rfc2049.txt
	 */
	private const ASCII_SAFE = [
		// ' (  )   +   ,   -   .   /   :   =   ?
		39, 40, 41, 43, 44, 45, 46, 47, 58, 61, 63,
		// digits
		48, 49, 50, 51, 52, 53, 54, 55, 56, 57,
		// upper-case letters
		65, 66, 67, 68, 69, 70, 71, 72, 73, 74, 75, 76, 77, 78, 79, 80, 81, 82, 83, 84, 85, 86, 87, 88, 89, 90,
		// lower-case letters
		97, 98, 99, 100, 101, 102, 103, 104, 105, 106, 107, 108, 109, 110, 111, 112, 113, 114, 115, 116, 117, 118, 119, 120, 121, 122,
	];

	//--------------------------------------------------------------------

	/**
	 * Character set.
	 *
	 * @var string
	 */
	private $charset;

	/**
	 * Mail encoding
	 *
	 * @var string
	 */
	private $encoding;

	/**
	 * Newline character sequence.
	 *
	 * @link http://www.ietf.org/rfc/rfc822.txt
	 * @var string
	 */
	private $newline;

	/**
	 * CRLF character sequence
	 *
	 * @link http://www.ietf.org/rfc/rfc822.txt
	 * @var string
	 */
	private $crlf;

	//--------------------------------------------------------------------

	/**
	 * Validates and stores config values.
	 *
	 * @param Mailer $config
	 */
	public function __construct(Mailer $config)
	{
		// Make sure charset is the correct case
		$this->charset = strtoupper($config->charset);

		// Validate newline values
		$this->newline = in_array($config->newline, self::NEWLINES, true) ? $config->newline : "\r\n";
		$this->crlf    = in_array($config->crlf, ["\r\n", "\r", "\n"], true) ? $config->crlf : "\r\n";

		// Check for charsets that need 7bit
		foreach (self::CHARSETS as $charset)
		{
			if (strpos($this->charset, $charset) === 0)
			{
				$this->encoding = '7bit';
				break;
			}
		}

		// Validate encoding
		$this->encoding = in_array($config->encoding, self::ENCODINGS, true) ? $config->encoding : '8bit';
	}

	/**
	 * Allows access to the stored, validated properties.
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public function __get(string $name): string
	{
		return $this->$name;
	}

	//--------------------------------------------------------------------

	/**
	 * Encodes the name portion of an Address, applying Q encoding
	 * if necessary, or escaping unsafe characters otherwise
	 *
	 * @param Address $address
	 *
	 * @return Address
	 */
	public function address(Address $address): Address
	{
		// The email has already been verified so encoding only applies to the display name
		if (empty($address->getName()))
		{
			return $address;
		}

		$name = $address->getName();

		// Use Q encoding if there are any characters that require it
		if (preg_match('/[\200-\377]/', $name))
		{
			$name = $this->Q($name);
		}
		// Otherwise, escape non-printing characters, slashes, and double quotes
		else
		{
			$name = addcslashes($name, "\0..\37\177'\"\\");
		}

		return new Address($address->getEmail(), $name);
	}

	/**
	 * Performs "Q Encoding" on a string for use in email headers.
	 * This is mostly straight from CodeIgniter 3.
	 *
	 * @see https://www.freesoft.org/CIE/RFC/1522/6.htm
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public function Q(string $string): string
	{
		$string = str_replace(["\r", "\n"], '', $string);
		$output = '=?' . $this->charset . '?Q?';
		$chars  = $this->charset === 'UTF-8' ? mb_strlen($string, 'UTF-8') : mb_strlen($string, '8bit');
		$length = mb_strlen($output, '8bit');

		for ($i = 0; $i < $chars; $i++)
		{
			$chr = ($this->charset === 'UTF-8' && extension_loaded('iconv'))
				? '=' . implode('=', str_split(strtoupper(bin2hex(iconv_substr($string, $i, 1, $this->charset))), 2))
				: '=' . strtoupper(bin2hex($string[$i]));

			$chrlen = mb_strlen($chr, '8bit');

			// RFC 2045 sets a limit of 76 characters per line, but leave space for ?= at the end of each line
			if ($length + $chrlen > 74)
			{
				$output .= '?=' . $this->crlf // EOL
					. ' =?' . $this->charset . '?Q?' . $chr; // New line

				$length = 6 + mb_strlen($this->charset, '8bit') + $chrlen; // Reset the length for the new line
			}
			else
			{
				$output .= $chr;
				$length += $chrlen;
			}
		}

		// End the header
		return $output . '?=';
	}
	/**
	 * Prepares a string for Quoted-Printable Content-Transfer-Encoding
	 * Refer to RFC 2045 http://www.ietf.org/rfc/rfc2045.txt
	 *
	 * @param string $str
	 *
	 * @return string
	 */
	public function quotedPrintable(string $str): string
	{
		// RFC 2045 specifies CRLF as "\r\n".
		// However, many developers choose to override that and violate
		// the RFC rules due to (apparently) a bug in MS Exchange,
		// which only works with "\n".
		if ($this->crlf === "\r\n")
		{
			return quoted_printable_encode($str);
		}

		// Reduce multiple spaces & remove nulls
		$str = preg_replace(['| +|', '/\x00+/'], [' ', ''], $str);

		// Standardize newlines
		if (strpos($str, "\r") !== false)
		{
			$str = str_replace(["\r\n", "\r"], "\n", $str);
		}

		$escape = '=';
		$output = '';

		foreach (explode("\n", $str) as $line)
		{
			$length = mb_strlen($line);
			$temp   = '';

			// Loop through each character in the line to add soft-wrap
			// characters at the end of a line " =\r\n" and add the newly
			// processed line(s) to the output (see comment on $crlf class property)
			for ($i = 0; $i < $length; $i ++)
			{
				// Grab the next character
				$char  = $line[$i];
				$ascii = ord($char);

				// Convert spaces and tabs but only if it's the end of the line
				if ($ascii === 32 || $ascii === 9)
				{
					if ($i === ($length - 1))
					{
						$char = $escape . sprintf('%02s', dechex($ascii));
					}
				}
				// DO NOT move this below the self::ASCII_SAFE line!
				//
				// = (equals) signs are allowed by RFC2049, but must be encoded
				// as they are the encoding delimiter!
				elseif ($ascii === 61)
				{
					$char = $escape . strtoupper(sprintf('%02s', dechex($ascii)));  // =3D
				}
				elseif (! in_array($ascii, self::ASCII_SAFE, true))
				{
					$char = $escape . strtoupper(sprintf('%02s', dechex($ascii)));
				}

				// If we're at the character limit, add the line to the output,
				// reset our temp variable, and keep on chuggin'
				if ((mb_strlen($temp) + mb_strlen($char)) >= 76)
				{
					$output .= $temp . $escape . $this->crlf;
					$temp    = '';
				}

				// Add the character to our temporary line
				$temp .= $char;
			}

			// Add our completed line to the output
			$output .= $temp . $this->crlf;
		}

		// get rid of extra CRLF tacked onto the end
		return mb_substr($output, 0, mb_strlen($this->crlf) * -1);
	}
}
