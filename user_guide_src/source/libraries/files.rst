##################
Working with Files
##################

CodeIgniter provides a File class that wraps the `SplFileInfo <https://www.php.net/manual/en/class.splfileinfo.php>`_ class
and provides some additional convenience methods. This class is the base class for :doc:`uploaded files </libraries/uploaded_files>`
and :doc:`images </libraries/images>`.

.. contents::
    :local:
    :depth: 2

Getting a File instance
***********************

You create a new File instance by passing in the path to the file in the constructor.
By default, the file does not need to exist. However, you can pass an additional argument of "true"
to check that the file exists and throw ``FileNotFoundException()`` if it does not.

.. literalinclude:: files/001.php

Taking Advantage of Spl
***********************

Once you have an instance, you have the full power of the SplFileInfo class at the ready, including:

.. literalinclude:: files/002.php

New Features
************

In addition to all of the methods in the SplFileInfo class, you get some new tools.

getRandomName()
===============

You can generate a cryptographically secure random filename, with the current timestamp prepended, with the ``getRandomName()``
method. This is especially useful to rename files when moving it so that the filename is unguessable:

.. literalinclude:: files/003.php

getSize()
=========

Returns the size of the uploaded file in bytes:

.. literalinclude:: files/004.php

getSizeByUnit()
===============

Returns the size of the uploaded file default in bytes. You can pass in either 'kb' or 'mb' as the first parameter to get
the results in kilobytes or megabytes, respectively:

.. literalinclude:: files/005.php

getMimeType()
=============

Retrieve the media type (mime type) of the file. Uses methods that are considered as secure as possible when determining
the type of file:

.. literalinclude:: files/006.php

guessExtension()
================

Attempts to determine the file extension based on the trusted ``getMimeType()`` method. If the mime type is unknown,
will return null. This is often a more trusted source than simply using the extension provided by the filename. Uses
the values in **app/Config/Mimes.php** to determine extension:

.. literalinclude:: files/007.php

Moving Files
============

Each file can be moved to its new location with the aptly named ``move()`` method. This takes the directory to move
the file to as the first parameter:

.. literalinclude:: files/008.php

By default, the original filename was used. You can specify a new filename by passing it as the second parameter:

.. literalinclude:: files/009.php

The move() method returns a new File instance that for the relocated file, so you must capture the result if the
resulting location is needed:

.. literalinclude:: files/010.php
