###############
Security Helper
###############

The Security Helper file contains security related functions.

.. contents::
    :local:
    :depth: 2

Loading this Helper
===================

This helper is loaded using the following code:

.. literalinclude:: security_helper/001.php

Available Functions
===================

The following functions are available:

.. php:function:: sanitize_filename($filename)

    :param    string    $filename: Filename
    :returns:    Sanitized file name
    :rtype:    string

    Provides protection against directory traversal.

    This function is an alias for ``\CodeIgniter\Security::sanitizeFilename()``.
    For more info, please see the :doc:`Security Library <../libraries/security>`
    documentation.

.. php:function:: strip_image_tags($str)

    :param    string    $str: Input string
    :returns:    The input string with no image tags
    :rtype:    string

    This is a security function that will strip image tags from a string.
    It leaves the image URL as plain text.

    Example:

    .. literalinclude:: security_helper/002.php

.. php:function:: encode_php_tags($str)

    :param    string    $str: Input string
    :returns:    Safely formatted string
    :rtype:    string

    This is a security function that converts PHP tags to entities.

    Example:

    .. literalinclude:: security_helper/003.php
