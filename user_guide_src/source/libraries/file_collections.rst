################
File Collections
################

Working with groups of files can be cumbersome, so the framework supplies the ``FileCollection`` class to facilitate
locating and working with groups of files across the filesystem.

.. contents::
    :local:
    :depth: 2

***********
Basic Usage
***********

At its most basic, ``FileCollection`` is an index
of files you set or build:

.. literalinclude:: files/011.php

After you have input the files you would like to work with you may remove files or use the filtering commands to remove
or retain files matching a certain regex or glob-style pattern:

.. literalinclude:: files/012.php

When your collection is complete, you can use ``get()`` to retrieve the final list of file paths, or take advantage of
``FileCollection`` being countable and iterable to work directly with each ``File``:

.. literalinclude:: files/013.php

Below are the specific methods for working with a ``FileCollection``.

*********************
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

***************
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

***************
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

****************
Retrieving Files
****************

get(): string[]
===============

Returns an array of all the loaded input files.

.. note:: ``FileCollection`` is an ``IteratorAggregate`` so you can work with it directly (e.g. ``foreach ($collection as $file)``).
