##########
Typography
##########

The Typography library contains methods that help you format text
in semantically relevant ways.

.. contents::
    :local:
    :depth: 2

*******************
Loading the Library
*******************

Like all services in CodeIgniter, it can be loaded via ``Config\Services``, though you usually will not need
to load it manually:

.. literalinclude:: typography/001.php

**************************
Available static functions
**************************

The following functions are available:

.. php:function:: autoTypography($str[, $reduce_linebreaks = false])

    :param    string    $str: Input string
    :param    bool    $reduce_linebreaks: Whether to reduce multiple instances of double newlines to two
    :returns:    HTML-formatted typography-safe string
    :rtype: string

    Formats text so that it is semantically and typographically correct
    HTML.

    Usage example:

    .. literalinclude:: typography/002.php

    .. note:: Typographic formatting can be processor intensive, particularly if
        you have a lot of content being formatted. If you choose to use this
        function you may want to consider :doc:`caching <../general/caching>` your
        pages.

.. php:function:: formatCharacters($str)

    :param    string    $str: Input string
    :returns:    String with formatted characters.
    :rtype:    string

    This function mainly converts double and single quotes
    to curly entities, but it also converts em-dashes,
    double spaces, and ampersands.

    Usage example:

    .. literalinclude:: typography/003.php

.. php:function:: nl2brExceptPre($str)

    :param    string    $str: Input string
    :returns:    String with HTML-formatted line breaks
    :rtype:    string

    Converts newlines to ``<br />`` tags unless they appear within ``<pre>`` tags.
    This function is identical to the native PHP ``nl2br()`` function,
    except that it ignores ``<pre>`` tags.

    Usage example:

    .. literalinclude:: typography/004.php
