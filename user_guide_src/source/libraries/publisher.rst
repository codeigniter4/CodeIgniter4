#########
Publisher
#########

The Publisher library provides a means to copy files within a project using robust detection and error checking.

.. contents::
    :local:
    :depth: 2

*******************
Loading the Library
*******************

Because Publisher instances are specific to their source and destination this library is not available
through ``Services`` but should be instantiated or extended directly. E.g.:

.. literalinclude:: publisher/001.php

*****************
Concept and Usage
*****************

``Publisher`` solves a handful of common problems when working within a backend framework:

* How do I maintain project assets with version dependencies?
* How do I manage uploads and other "dynamic" files that need to be web accessible?
* How can I update my project when the framework or modules change?
* How can components inject new content into existing projects?

At its most basic, publishing amounts to copying a file or files into a project. ``Publisher`` extends ``FileCollection``
to enact fluent-style command chaining to read, filter, and process input files, then copies or merges them into the target destination.
You may use ``Publisher`` on demand in your Controllers or other components, or you may stage publications by extending
the class and leveraging its discovery with ``spark publish``.

On Demand
=========

Access ``Publisher`` directly by instantiating a new instance of the class:

.. literalinclude:: publisher/002.php

By default the source and destination will be set to ``ROOTPATH`` and ``FCPATH`` respectively, giving ``Publisher``
easy access to take any file from your project and make it web-accessible. Alternatively you may pass a new source
or source and destination into the constructor:

.. literalinclude:: publisher/003.php

Once all the files are staged use one of the output commands (**copy()** or **merge()**) to process the staged files
to their destination(s):

.. literalinclude:: publisher/004.php

See the :ref:`reference` for a full description of available methods.

Automation and Discovery
========================

You may have regular publication tasks embedded as part of your application deployment or upkeep. ``Publisher`` leverages
the powerful ``Autoloader`` to locate any child classes primed for publication:

.. literalinclude:: publisher/005.php

By default ``discover()`` will search for the "Publishers" directory across all namespaces, but you may specify a
different directory and it will return any child classes found:

.. literalinclude:: publisher/006.php

Most of the time you will not need to handle your own discovery, just use the provided "publish" command:

.. code-block:: console

    php spark publish

By default on your class extension ``publish()`` will add all files from your ``$source`` and merge them
out to your destination, overwriting on collision.

Security
========

In order to prevent modules from injecting malicious code into your projects, ``Publisher`` contains a config file
that defines which directories and file patterns are allowed as destinations. By default, files may only be published
to your project (to prevent access to the rest of the filesystem), and the **public/** folder (``FCPATH``) will only
receive files with the following extensions:

* Web assets: css, scss, js, map
* Non-executable web files: htm, html, xml, json, webmanifest
* Fonts: ttf, eot, woff, woff2
* Images: gif, jpg, jpeg, tif, tiff, png, webp, bmp, ico, svg

If you need to add or adjust the security for your project then alter the ``$restrictions`` property of ``Config\Publisher`` in **app/Config/Publisher.php**.

********
Examples
********

Here are a handful of example use cases and their implementations to help you get started publishing.

File Sync Example
=================

You want to display a "photo of the day" image on your homepage. You have a feed for daily photos but you
need to get the actual file into a browsable location in your project at **public/images/daily_photo.jpg**.
You can set up :doc:`Custom Command </cli/cli_commands>` to run daily that will handle this for you:

.. literalinclude:: publisher/007.php

Now running ``spark publish:daily`` will keep your homepage's image up-to-date. What if the photo is
coming from an external API? You can use ``addUri()`` in place of ``addPath()`` to download the remote
resource and publish it out instead:

.. literalinclude:: publisher/008.php

Asset Dependencies Example
==========================

You want to integrate the frontend library "Bootstrap" into your project, but the frequent updates makes it a hassle
to keep up with. You can create a publication definition in your project to sync frontend assets by extending
``Publisher`` in your project. So **app/Publishers/BootstrapPublisher.php** might look like this:

.. literalinclude:: publisher/009.php

.. note:: Directory ``$destination`` must be created before executing the command.

Now add the dependency via Composer and call ``spark publish`` to run the publication:

.. code-block:: console

    composer require twbs/bootstrap
    php spark publish

... and you'll end up with something like this::

    public/.htaccess
    public/favicon.ico
    public/index.php
    public/robots.txt
    public/
        bootstrap/
            css/
                bootstrap.min.css
                bootstrap-utilities.min.css.map
                bootstrap-grid.min.css
                bootstrap.rtl.min.css
                bootstrap.min.css.map
                bootstrap-reboot.min.css
                bootstrap-utilities.min.css
                bootstrap-reboot.rtl.min.css
                bootstrap-grid.min.css.map
            js/
                bootstrap.esm.min.js
                bootstrap.bundle.min.js.map
                bootstrap.bundle.min.js
                bootstrap.min.js
                bootstrap.esm.min.js.map
                bootstrap.min.js.map

Module Deployment Example
=========================

You want to allow developers using your popular authentication module the ability to expand on the default behavior
of your Migration, Controller, and Model. You can create your own module "publish" command to inject these components
into an application for use:

.. literalinclude:: publisher/010.php

Now when your module users run ``php spark auth:publish`` they will have the following added to their project::

    app/Controllers/AuthController.php
    app/Database/Migrations/2017-11-20-223112_create_auth_tables.php.php
    app/Models/LoginModel.php
    app/Models/UserModel.php

.. _reference:

*****************
Library Reference
*****************

.. note:: ``Publisher`` is an extension of :doc:`FileCollection </libraries/files>` so has access to all those methods for reading and filtering files.

Support Methods
===============

[static] discover(string $directory = 'Publishers'): Publisher[]
----------------------------------------------------------------

Discovers and returns all Publishers in the specified namespace directory. For example, if both
**app/Publishers/FrameworkPublisher.php** and **myModule/src/Publishers/AssetPublisher.php** exist and are
extensions of ``Publisher`` then ``Publisher::discover()`` would return an instance of each.

publish(): bool
---------------

Processes the full input-process-output chain. By default this is the equivalent of calling ``addPath($source)``
and ``merge(true)`` but child classes will typically provide their own implementation. ``publish()`` is called
on all discovered Publishers when running ``spark publish``.
Returns success or failure.

getScratch(): string
--------------------

Returns the temporary workspace, creating it if necessary. Some operations use intermediate storage to stage
files and changes, and this provides the path to a transient, writable directory that you may use as well.

getErrors(): array<string, Throwable>
-------------------------------------

Returns any errors from the last write operation. The array keys are the files that caused the error, and the
values are the Throwable that was caught. Use ``getMessage()`` on the Throwable to get the error message.

addPath(string $path, bool $recursive = true)
---------------------------------------------

Adds all files indicated by the relative path. Path is a reference to actual files or directories relative
to ``$source``. If the relative path resolves to a directory then ``$recursive`` will include sub-directories.

addPaths(array $paths, bool $recursive = true)
----------------------------------------------

Adds all files indicated by the relative paths. Paths are references to actual files or directories relative
to ``$source``. If the relative path resolves to a directory then ``$recursive`` will include sub-directories.

addUri(string $uri)
-------------------

Downloads the contents of a URI using ``CURLRequest`` into the scratch workspace then adds the resulting
file to the list.

addUris(array $uris)
--------------------

Downloads the contents of URIs using ``CURLRequest`` into the scratch workspace then adds the resulting
files to the list.

.. note:: The CURL request made is a simple ``GET`` and uses the response body for the file contents. Some
    remote files may need a custom request to be handled properly.

Outputting Files
================

wipe()
------

Removes all files, directories, and sub-directories from ``$destination``.

.. important:: Use wisely.

copy(bool $replace = true): bool
--------------------------------

Copies all files into the ``$destination``. This does not recreate the directory structure, so every file
from the current list will end up in the same destination directory. Using ``$replace`` will cause files
to overwrite when there is already an existing file. Returns success or failure, use ``getPublished()``
and ``getErrors()`` to troubleshoot failures.
Be mindful of duplicate basename collisions, for example:

.. literalinclude:: publisher/011.php

merge(bool $replace = true): bool
---------------------------------

Copies all files into the ``$destination`` in appropriate relative sub-directories. Any files that
match ``$source`` will be placed into their equivalent directories in ``$destination``, effectively
creating a "mirror" or "rsync" operation. Using ``$replace`` will cause files
to overwrite when there is already an existing file; since directories are merged this will not
affect other files in the destination. Returns success or failure, use ``getPublished()`` and
``getErrors()`` to troubleshoot failures.

Example:

.. literalinclude:: publisher/012.php

.. _publisher-modifying-files:

Modifying Files
===============

replace(string $file, array $replaces): bool
--------------------------------------------

.. versionadded:: 4.3.0

Replaces the ``$file`` contents. The second parameter ``$replaces`` array specifies the search strings as keys and the replacements as values.

.. literalinclude:: publisher/013.php

addLineAfter(string $file, string $line, string $after): bool
-------------------------------------------------------------

.. versionadded:: 4.3.0

Adds ``$line`` after a line with specific string ``$after``.

.. literalinclude:: publisher/014.php

addLineBefore(string $file, string $line, string $after): bool
--------------------------------------------------------------

.. versionadded:: 4.3.0

Adds ``$line`` before a line with specific string ``$after``.

.. literalinclude:: publisher/015.php
