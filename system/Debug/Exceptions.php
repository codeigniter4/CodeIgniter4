<?php namespace CodeIgniter\Debug;
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */

require __DIR__.'/CustomExceptions.php';

/**
 * Exceptions manager
 */
class Exceptions
{

	/**
	 * Nesting level of the output buffering mechanism
	 *
	 * @var    int
	 */
	public $ob_level;

	/**
	 * The path to the directory containing the
	 * cli and html error view directories.
	 *
	 * @var string
	 */
	protected $viewPath;

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @param \Config\App $config
	 */
	public function __construct(\Config\App $config)
	{
		$this->ob_level = ob_get_level();

		$this->viewPath = rtrim($config->errorViewPath, '/ ').'/';
	}

	//--------------------------------------------------------------------

	/**
	 * Responsible for registering the error, exception and shutdown
	 * handling of our application.
	 */
	public function initialize()
	{
		//Set the Exception Handler
		set_exception_handler([$this, 'exceptionHandler']);

		// Set the Error Handler
		set_error_handler([$this, 'errorHandler']);

		// Set the handler for shutdown to catch Parse errors
		// Do we need this in PHP7?
		register_shutdown_function([$this, 'shutdownHandler']);
	}

	//--------------------------------------------------------------------

	/**
	 * Catches any uncaught errors and exceptions, including most Fatal errors
	 * (Yay PHP7!). Will log the error, display it if display_errors is on,
	 * and fire an event that allows custom actions to be taken at this point.
	 *
	 * @param \Throwable $exception
	 */
	public function exceptionHandler(\Throwable $exception)
	{
		// Get Exception Info - these are available
		// directly in the template that's displayed.
		$type    = get_class($exception);
		$codes   = $this->determineCodes($exception);
		$code    = $codes[0];
		$exit    = $codes[1];
		$code    = $exception->getCode();
		$message = $exception->getMessage();
		$file    = $exception->getFile();
		$line    = $exception->getLine();
		$trace   = $exception->getTrace();
		$title   = get_class($exception);

		if (empty($message))
		{
			$message = '(null)';
		}

		// Log it

		// Fire an Event
		$templates_path = $this->viewPath;
		if (empty($templates_path))
		{
			$templates_path = APPPATH.'Views/errors/';
		}

		if (is_cli())
		{
			$templates_path .= 'cli/';
		}
		else
		{
			header('HTTP/1.1 500 Internal Server Error', true, 500);
			$templates_path .= 'html/';
		}

		$view = $this->determineView($exception, $templates_path);

		if (ob_get_level() > $this->ob_level + 1)
		{
			ob_end_flush();
		}

		ob_start();
		include($templates_path.$view);
		$buffer = ob_get_contents();
		ob_end_clean();
		echo $buffer;

		exit($exit);
	}

	//--------------------------------------------------------------------

	/**
	 * Even in PHP7, some errors make it through to the errorHandler, so
	 * convert these to Exceptions and let the exception handler log it and
	 * display it.
	 *
	 * This seems to be primarily when a user triggers it with trigger_error().
	 *
	 * @param int         $severity
	 * @param string      $message
	 * @param string|null $file
	 * @param int|null    $line
	 * @param null        $context
	 *
	 * @throws \ErrorException
	 */
	public function errorHandler(int $severity, string $message, string $file = null, int $line = null, $context = null)
	{
		// Convert it to an exception and pass it along.
		throw new \ErrorException($message, 0, $severity, $file, $line);
	}
	
	//--------------------------------------------------------------------

	/**
	 * Checks to see if any errors have happened during shutdown that
	 * need to be caught and handle them.
	 */
	public function shutdownHandler()
	{
		$error = error_get_last();

		// If we've got an error that hasn't been displayed, then convert
		// it to an Exception and use the Exception handler to display it
		// to the user.
		if (! is_null($error))
		{
			// Fatal Error?
			if (in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE]))
			{
				$this->exceptionHandler(new \ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));
			}
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Determines the view to display based on the exception thrown,
	 * whether an HTTP or CLI request, etc.
	 *
	 * @param \Throwable $exception
	 * @param string     $template_path
	 *
	 * @return string       The path and filename of the view file to use
	 */
	protected function determineView(\Throwable $exception, string $template_path): string
	{
		// Production environments should have a custom exception file.
		$view = 'production.php';
		$template_path = rtrim($template_path, '/ ').'/';

		if (str_ireplace(['off', 'none', 'no', 'false', 'null'], '', ini_get('display_errors')))
		{
			$view = 'error_exception.php';
		}

		// 404 Errors
		if ($exception instanceof \CodeIgniter\PageNotFoundException)
		{
			return 'error_404.php';
		}

		// Allow for custom views based upon the status code
		else if (is_file($template_path.'error_'.$exception->getCode().'.php'))
		{
			return 'error_'.$exception->getCode().'.php';
		}

		return $view;
	}

	//--------------------------------------------------------------------

	/**
	 * Determines the HTTP status code and the exit status code for this request.
	 *
	 * @param \Throwable $exception
	 *
	 * @return array
	 */
	protected function determineCodes(\Throwable $exception): array
	{
		$statusCode = abs($exception->getCode());

		if ($statusCode < 100)
		{
			$exitStatus = $statusCode + EXIT__AUTO_MIN; // 9 is EXIT__AUTO_MIN
			if ($exitStatus > EXIT__AUTO_MAX) // 125 is EXIT__AUTO_MAX
			{
				$exitStatus = EXIT_ERROR; // EXIT_ERROR
			}
			$statusCode = 500;
		}
		else
		{
			$exitStatus = 1; // EXIT_ERROR
		}

		return [
			$statusCode ?? 500,
		    $exitStatus
		];
	}

	//--------------------------------------------------------------------

	//--------------------------------------------------------------------
	// Display Methods
	//--------------------------------------------------------------------

	/**
	 * Clean Path
	 *
	 * This makes nicer looking paths for the error output.
	 *
	 * @param    string $file
	 *
	 * @return    string
	 */
	public static function cleanPath($file)
	{
		if (strpos($file, APPPATH) === 0)
		{
			$file = 'APPPATH/'.substr($file, strlen(APPPATH));
		}
		elseif (strpos($file, BASEPATH) === 0)
		{
			$file = 'BASEPATH/'.substr($file, strlen(BASEPATH));
		}
		elseif (strpos($file, FCPATH) === 0)
		{
			$file = 'FCPATH/'.substr($file, strlen(FCPATH));
		}

		return $file;
	}

	//--------------------------------------------------------------------

	/**
	 * Describes memory usage in real-world units. Intended for use
	 * with memory_get_usage, etc.
	 *
	 * @param $bytes
	 *
	 * @return string
	 */
	public static function describeMemory(int $bytes): string
	{
		if ($bytes < 1024)
		{
			return $bytes.'B';
		}
		else if ($bytes < 1048576)
		{
			return round($bytes/1024, 2).'KB';
		}

		return round($bytes/1048576, 2).'MB';
	}

	//--------------------------------------------------------------------


	/**
	 * Creates a syntax-highlighted version of a PHP file.
	 *
	 * @param     $file
	 * @param     $lineNumber
	 * @param int $lines
	 *
	 * @return bool|string
	 */
	public static function highlightFile($file, $lineNumber, $lines = 15)
	{
		if (empty ($file) || ! is_readable($file))
		{
			return false;
		}

		// Set our highlight colors:
		if (function_exists('ini_set'))
		{
			ini_set('highlight.comment', '#767a7e; font-style: italic');
			ini_set('highlight.default', '#c7c7c7');
			ini_set('highlight.html', '#06B');
			ini_set('highlight.keyword', '#f1ce61;');
			ini_set('highlight.string', '#869d6a');
		}

		try
		{
			$source = file_get_contents($file);
		}
		catch (\Throwable $e)
		{
			return false;
		}

		$source = str_replace(["\r\n", "\r"], "\n", $source);
		$source = explode("\n", highlight_string($source, true));
		$source = str_replace('<br />', "\n", $source[1]);

		$source = explode("\n", str_replace("\r\n", "\n", $source));

		// Get just the part to show
		$start = $lineNumber - (int)round($lines / 2);
		$start = $start < 0 ? 0 : $start;

		// Get just the lines we need to display, while keeping line numbers...
		$source = array_splice($source, $start, $lines, true);

		// Used to format the line number in the source
		$format = '% '.strlen($start + $lines).'d';

		$out = '';
		// Because the highlighting may have an uneven number
		// of open and close span tags on one line, we need
		// to ensure we can close them all to get the lines
		// showing correctly.
		$spans = 1;

		foreach ($source as $n => $row)
		{
			$spans += substr_count($row, '<span') - substr_count($row, '</span');
			$row = str_replace(["\r", "\n"], ['', ''], $row);

			if (($n+$start+1) == $lineNumber)
			{
				preg_match_all('#<[^>]+>#', $row, $tags);
				$out .= sprintf("<span class='line highlight'><span class='number'>{$format}</span> %s\n</span>%s",
						$n + $start + 1,
						strip_tags($row),
						implode('', $tags[0])
				);
			}
			else
			{
				$out .= sprintf('<span class="line"><span class="number">'.$format.'</span> %s', $n + $start +1, $row) ."\n";
			}
		}

		$out .= str_repeat('</span>', $spans);

		return '<pre><code>'.$out.'</code></pre>';
	}

	//--------------------------------------------------------------------

}
