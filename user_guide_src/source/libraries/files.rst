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

################
File Collections
################

Working with groups of files can be cumbersome, so the framework supplies the ``FileCollection`` class to facilitate
locating and working with groups of files across the filesystem. At its most basic, ``FileCollection`` is an index
of files you set or build:

.. literalinclude:: files/011.php

After you have input the files you would like to work with you may remove files or use the filtering commands to remove
or retain files matching a certain regex or glob-style pattern:

.. literalinclude:: files/012.php

When your collection is complete, you can use ``get()`` to retrieve the final list of file paths, or take advantage of
``FileCollection`` being countable and iterable to work directly with each ``File``:

.. literalinclude:: files/013.php

Below are the specific methods for working with a ``FileCollection``.

Starting a Collection
*********************

__construct(string[] $files = [])
=================================

The constructor accepts an optional array of file paths to use as the initial collection. These are passed to
``add()`` so any files supplied by child classes in the ``$files`` will remain.

define()
========

Allows child classes to define their own initial files. This method is called by the constructor and allows
predefined collections without having to use their methods. Example:

.. literalinclude:: files/014.php

Now you may use the ``ConfigCollection`` anywhere in your project to access all App Config files without
having to re-call the collection methods every time.

set(array $files)
=================

Sets the list of input files to the provided string array of file paths. This will remove any existing
files from the collection, so ``$collection->set([])`` is essentially a hard reset.

Inputting Files
***************

add(string[]|string $paths, bool $recursive = true)
===================================================

Adds all files indicated by the path or array of paths. If the path resolves to a directory then ``$recursive``
will include sub-directories.

addFile(string $file) / addFiles(array $files)
==============================================

Adds the file or files to the current list of input files. Files are absolute paths to actual files.

removeFile(string $file) / removeFiles(array $files)
====================================================

Removes the file or files from the current list of input files.

addDirectory(string $directory, bool $recursive = false)
========================================================
addDirectories(array $directories, bool $recursive = false)
===========================================================

Adds all files from the directory or directories, optionally recursing into sub-directories. Directories are
absolute paths to actual directories.

Filtering Files
***************

removePattern(string $pattern, string $scope = null)
====================================================
retainPattern(string $pattern, string $scope = null)
====================================================

Filters the current file list through the pattern (and optional scope), removing or retaining matched
files. ``$pattern`` may be a complete regex (like ``'#[A-Za-z]+\.php#'``) or a pseudo-regex similar
to ``glob()`` (like ``*.css``).
If a ``$scope`` is provided then only files in or under that directory will be considered (i.e. files
outside of ``$scope`` are always retained). When no scope is provided then all files are subject.

Examples:

.. literalinclude:: files/015.php

Retrieving Files
****************

get(): string[]
===============

Returns an array of all the loaded input files.

.. note:: ``FileCollection`` is an ``IteratorAggregate`` so you can work with it directly (e.g. ``foreach ($collection as $file)``).
