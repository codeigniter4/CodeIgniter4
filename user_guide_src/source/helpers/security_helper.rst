###############
Security Helper
###############

The Security Helper file contains security related functions.

.. contents::
  :local:

Loading this Helper
===================

This helper is loaded using the following code::

	helper('security');

Available Functions
===================

The following functions are available:

.. php:function:: sanitize_filename($filename)

	:param	string	$filename: Filename
    	:returns:	Sanitized file name
    	:rtype:	string

    	Provides protection against directory traversal.

    	This function is an alias for ``\CodeIgniter\Security::sanitize_filename()``.
	For more info, please see the :doc:`Security Library <../libraries/security>`
	documentation.

.. php:function:: strip_image_tags($str)

	:param	string	$str: Input string
    	:returns:	The input string with no image tags
    	:rtype:	string

    	This is a security function that will strip image tags from a string.
    	It leaves the image URL as plain text.

    	Example::

		$string = strip_image_tags($string);

.. php:function:: encode_php_tags($str)

	:param	string	$str: Input string
    	:returns:	Safely formatted string
    	:rtype:	string

    	This is a security function that converts PHP tags to entities.

	Example::

		$string = encode_php_tags($string);
