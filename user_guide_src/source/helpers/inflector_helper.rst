################
Inflector Helper
################

The Inflector Helper file contains functions that permit you to change
**English** words to plural, singular, camel case, etc.

.. contents::
  :local:

.. raw:: html

  <div class="custom-index container"></div>

Loading this Helper
===================

This helper is loaded using the following code::

	helper('inflector');

Available Functions
===================

The following functions are available:

.. php:function:: singular($string)

	:param	string	$string: Input string
	:returns:	A singular word
	:rtype:	string

	Changes a plural word to singular. Example::

		echo singular('dogs'); // Prints 'dog'

.. php:function:: plural($string)

	:param	string	$string: Input string
	:returns:	A plural word
	:rtype:	string

	Changes a singular word to plural. Example::

		echo plural('dog'); // Prints 'dogs'

.. php:function:: camelize($string)

	:param	string	$string: Input string
	:returns:	Camelized string
	:rtype:	string

	Changes a string of words separated by spaces or underscores to camel
	case. Example::

		echo camelize('my_dog_spot'); // Prints 'myDogSpot'

.. php:function:: underscore($string)

	:param	string	$string: Input string
	:returns:	String containing underscores instead of spaces
	:rtype:	string

	Takes multiple words separated by spaces and underscores them.
	Example::

		echo underscore('my dog spot'); // Prints 'my_dog_spot'

.. php:function:: humanize($string[, $separator = '_'])

	:param	string	$string: Input string
	:param	string	$separator: Input separator
	:returns:	Humanized string
	:rtype:	string

	Takes multiple words separated by underscores and adds spaces between
	them. Each word is capitalized.

	Example::

		echo humanize('my_dog_spot'); // Prints 'My Dog Spot'

	To use dashes instead of underscores::

		echo humanize('my-dog-spot', '-'); // Prints 'My Dog Spot'

.. php:function:: is_pluralizable($word)

	:param	string	$word: Input string
	:returns:	TRUE if the word is countable or FALSE if not
	:rtype:	bool

	Checks if the given word has a plural version. Example::

		is_pluralizable('equipment'); // Returns FALSE

.. php:function:: dasherize($string)

	:param	string	$string: Input string
	:returns:	Dasherized string
	:rtype:	string

	Replaces underscores with dashes in the string. Example::

		dasherize('hello_world'); // Returns 'hello-world'

.. php:function:: ordinal($integer)

	:param	int	$integer: The integer to determine the suffix
	:returns:	Ordinal suffix
	:rtype:	string

	Returns the suffix that should be added to a
	number to denote the position such as
	1st, 2nd, 3rd, 4th. Example::

		ordinal(1); // Returns 'st'

.. php:function:: ordinalize($integer)

	:param	int	$integer: The integer to ordinalize
	:returns:	Ordinalized integer
	:rtype:	string

	Turns a number into an ordinal string used
	to denote the position such as 1st, 2nd, 3rd, 4th.
	Example::

		ordinalize(1); // Returns '1st'