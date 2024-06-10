################
Inflector Helper
################

The Inflector Helper file contains functions that permit you to change
**English** words to plural, singular, camel case, etc.

.. contents::
    :local:
    :depth: 2

Loading this Helper
===================

This helper is loaded using the following code:

.. literalinclude:: inflector_helper/001.php

Available Functions
===================

The following functions are available:

.. php:function:: singular($string)

    :param    string    $string: Input string
    :returns:    A singular word
    :rtype:    string

    Changes a plural word to singular. Example:

    .. literalinclude:: inflector_helper/002.php

.. php:function:: plural($string)

    :param    string    $string: Input string
    :returns:    A plural word
    :rtype:    string

    Changes a singular word to plural. Example:

    .. literalinclude:: inflector_helper/003.php

.. php:function:: counted($count, $string)

    :param    int     $count:  Number of items
    :param    string    $string: Input string
    :returns:    A singular or plural phrase
    :rtype:    string

    Changes a word and its count to a phrase. Example:

    .. literalinclude:: inflector_helper/004.php

.. php:function:: camelize($string)

    :param    string    $string: Input string
    :returns:    Camel case string
    :rtype:    string

    Changes a string of words separated by spaces or underscores to camel
    case. Example:

    .. literalinclude:: inflector_helper/005.php

.. php:function:: pascalize($string)

    :param    string    $string: Input string
    :returns:    Pascal case string
    :rtype:    string

    Changes a string of words separated by spaces or underscores to Pascal
    case, which is camel case with the first letter capitalized. Example:

    .. literalinclude:: inflector_helper/006.php

.. php:function:: underscore($string)

    :param    string    $string: Input string
    :returns:    String containing underscores instead of spaces
    :rtype:    string

    Takes multiple words separated by spaces and underscores them.
    Example:

    .. literalinclude:: inflector_helper/007.php

.. php:function:: decamelize($string)

    :param    string    $string: Input string
    :returns:    String containing underscores between words
    :rtype:    string

    Takes multiple words in camelCase or PascalCase and converts them to snake_case.
    Example:

    .. literalinclude:: inflector_helper/014.php

.. php:function:: humanize($string[, $separator = '_'])

    :param    string    $string: Input string
    :param    string    $separator: Input separator
    :returns:    Humanized string
    :rtype:    string

    Takes multiple words separated by underscores and adds spaces between
    them. Each word is capitalized.

    Example:

    .. literalinclude:: inflector_helper/008.php

    To use dashes instead of underscores:

    .. literalinclude:: inflector_helper/009.php

.. php:function:: is_pluralizable($word)

    :param    string    $word: Input string
    :returns:    true if the word is countable or false if not
    :rtype:    bool

    Checks if the given word has a plural version. Example:

    .. literalinclude:: inflector_helper/010.php

.. php:function:: dasherize($string)

    :param    string    $string: Input string
    :returns:    Dasherized string
    :rtype:    string

    Replaces underscores with dashes in the string. Example:

    .. literalinclude:: inflector_helper/011.php

.. php:function:: ordinal($integer)

    :param    int    $integer: The integer to determine the suffix
    :returns:    Ordinal suffix
    :rtype:    string

    Returns the suffix that should be added to a
    number to denote the position such as
    1st, 2nd, 3rd, 4th. Example:

    .. literalinclude:: inflector_helper/012.php

.. php:function:: ordinalize($integer)

    :param    int    $integer: The integer to ordinalize
    :returns:    Ordinalized integer
    :rtype:    string

    Turns a number into an ordinal string used
    to denote the position such as 1st, 2nd, 3rd, 4th.
    Example:

    .. literalinclude:: inflector_helper/013.php
