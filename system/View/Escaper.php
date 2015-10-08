<?php

namespace
{

	use CodeIgniter\View\Escaper;

	if ( ! function_exists('esc'))
	{
		/**
		 * Performs simple auto-escaping of data for security reasons.
		 * Might consider making this more complex at a later date.
		 *
		 * If $data is a string, then it simply escapes and returns it.
		 * If $data is an array, then it loops over it, escaping each
		 * 'value' of the key/value pairs.
		 *
		 * Valid context values: html, js, css, url, raw, null
		 *
		 * @param string|array $data
		 * @param string       $context
		 *
		 * @return $data
		 */
		function esc($data, $context = 'html')
		{
			if (is_array($data))
			{
				foreach ($data as $key => &$value)
				{
					$value = esc($value, $context);
				}
			}

			if (is_string($data))
			{
				$context = strtoupper($context);

				// Provide a way to NOT escape data since
				// this could be called automatically by
				// the View library.
				if (empty($context) || $context == 'RAW')
				{
					return $data;
				}

				if ( ! in_array($context, ['HTML', 'JS', 'CSS', 'URL']))
				{
					throw new \InvalidArgumentException('Invalid escape context provided.');
				}

				$method = 'escape'.$context;

				$data = Escaper::$method($data);
			}

			return $data;
		}
	}
}

//--------------------------------------------------------------------

namespace CodeIgniter\View
{

	/**
	 * Class Escaper
	 *
	 * Provides common escaping routines for view data.
	 * The actual methods used to filter were borrowed from
	 * the Nette Framework. (https://nette.org/en/)
	 *
	 * @package CodeIgniter\View
	 */
	class Escaper
	{

		/**
		 * Escapes a string for use inside of standard HTML.
		 *
		 * @param string $str
		 *
		 * @return string
		 */
		public static function escapeHTML(string $str): string
		{
			return htmlspecialchars($str, ENT_SUBSTITUTE, 'UTF-8');
		}

		//--------------------------------------------------------------------

		/**
		 * Escapes a string for use as an href value.
		 *
		 * @param string $str
		 *
		 * @return string
		 */
		public static function escapeURL(string $str): string
		{
			return preg_match('~^(?:(?:https?|ftp)://[^@]+(?:/.*)?|mailto:.+|[/?#].*|[^:]+)\z~i', $str) ? $str : '';
		}

		//--------------------------------------------------------------------

		/**
		 * Escapes a string for use within CSS.
		 *
		 * @param string $str
		 *
		 * @return string
		 */
		public static function escapeCSS(string $str): string
		{
			// http://www.w3.org/TR/2006/WD-CSS21-20060411/syndata.html#q6
			return addcslashes($str, "\x00..\x1F!\"#$%&'()*+,./:;<=>?@[\\]^`{|}~");
		}

		//--------------------------------------------------------------------

		/**
		 * Escapes a string for use within Javascript.
		 *
		 * @param string $str
		 *
		 * @return string
		 */
		public static function escapeJS(string $str): string
		{
			$json = json_encode($str, JSON_UNESCAPED_UNICODE);

			if ($error = json_last_error())
			{
				throw new \RuntimeException(json_last_error_msg(), $error);
			}

			return str_replace(["\xe2\x80\xa8", "\xe2\x80\xa9", ']]>', '<!'], ['\u2028', '\u2029', ']]\x3E', '\x3C!'],
				$json);
		}

		//--------------------------------------------------------------------

	}
}
