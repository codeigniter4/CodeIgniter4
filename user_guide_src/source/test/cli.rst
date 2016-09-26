CLI Class
##########

Class CLI

Tools to interact with that request since CLI requests are not
static like HTTP requests might be.

Portions of this code were initially from the FuelPHP Framework,
version 1.7.x, and used here under the MIT license they were
originally made available under.

http://fuelphp.com


.. php:class:: CodeIgniter\\CLI\\CLI

	.. php:method:: init (  )



		Static "constructor".


	.. php:method:: input ( [ string $prefix ] )

		:param string $prefix: 


		Get input from the shell, using readline or the standard STDIN

		Named options must be in the following formats:
		php index.php user -v --v -name=John --name=John




	.. php:method:: prompt (  )



		Asks the user for input.  This can have either 1 or 2 arguments.

		Usage:

		// Waits for any key press
		CLI::prompt();

		// Takes any input
		$color = CLI::prompt('What is your favorite color?');

		// Takes any input, but offers default
		$color = CLI::prompt('What is your favourite color?', 'white');

		// Will only accept the options in the array
		$ready = CLI::prompt('Are you ready?', array('y','n'));



	.. php:method:: write ( string $text [ string $foreground [, string $background ]] )

		:param string $text: 
		:param string $foreground: 
		:param string $background: 


		Outputs a string to the cli.     If you send an array it will implode them
		with a line break.



	.. php:method:: error ( string $text [ string $foreground [, string $background ]] )

		:param string $text: 
		:param string $foreground: 
		:param string $background: 


		Outputs an error to the CLI using STDERR instead of STDOUT



	.. php:method:: beep ( [ int $num ] )

		:param int $num: 


		Beeps a certain number of times.



	.. php:method:: wait ( int $seconds [ bool $countdown ] )

		:param int $seconds: 
		:param bool $countdown: 


		Waits a certain number of seconds, optionally showing a wait message and
		waiting for a key press.



	.. php:method:: isWindows (  )



		if operating system === windows


	.. php:method:: newLine ( [ int $num ] )

		:param int $num: 


		Enter a number of empty lines




	.. php:method:: clearScreen (  )



		Clears the screen of output



	.. php:method:: color ( string $text string $foreground [ string $background [, string $format ]] )

		:param string $text: 
		:param string $foreground: 
		:param string $background: 
		:param string $format: 


		Returns the given text with the correct color codes for a foreground and
		optionally a background color.




	.. php:method:: getWidth ( [ int $default ] )

		:param int $default: 


		Attempts to determine the width of the viewable CLI window.
		This only works on \*nix-based systems, so return a sane default
		for Windows environments.




	.. php:method:: getHeight ( [ int $default ] )

		:param int $default: 


		Attempts to determine the height of the viewable CLI window.
		This only works on \*nix-based systems, so return a sane default
		for Windows environments.




	.. php:method:: showProgress ( [ int $thisStep [, int $totalSteps ]] )

		:param int $thisStep: 
		:param int $totalSteps: 


		Displays a progress bar on the CLI. You must call it repeatedly
		to update it. Set $thisStep = false to erase the progress bar.



	.. php:method:: wrap ( [ string $string [, int $max [, int $pad_left ]]] )

		:param string $string: 
		:param int $max: 
		:param int $pad_left: 


		Takes a string and writes it to the command line, wrapping to a maximum
		width. If no maximum width is specified, will wrap to the window's max
		width.

		If an int is passed into $pad_left, then all strings after the first
		will padded with that many spaces to the left. Useful when printing
		short descriptions that need to start on an existing line.





