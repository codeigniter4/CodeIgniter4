#############################
Upgrading from 4.7.4 to 4.7.5
#############################

Please refer to the upgrade instructions corresponding to your installation method.

- :ref:`Composer Installation App Starter Upgrading <app-starter-upgrading>`
- :ref:`Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading <adding-codeigniter4-upgrading>`
- :ref:`Manual Installation Upgrading <installing-manual-upgrading>`

.. contents::
    :local:
    :depth: 2

**********************
Mandatory File Changes
**********************

****************
Breaking Changes
****************

.. _upgrade-475-view-parser-values:

View Parser No Longer Parses Substituted Values
===============================================

For security reasons, the ``Parser`` now treats every substituted value strictly as
data. A value that contains Parser syntax, such as ``{name}``, ``{!name!}`` or
``{name|upper}``, is rendered literally and is never parsed again by a later
substitution pass. See the
`Security advisory GHSA-vgrf-mv2j-gp8w <https://github.com/codeigniter4/CodeIgniter4/security/advisories/GHSA-vgrf-mv2j-gp8w>`_
for more information.

Previously, such a value could be parsed again, depending on the order of the keys in
the data array. This behavior was never documented, but code that relied on it will
now output the placeholders literally. For example, the following used to render
``<p>Hello, Bob!</p>`` and now renders ``<p>Hello, {name}!</p>``:

.. code-block:: php

    $parser->setData([
        'greeting' => 'Hello, {name}!',
        'name'     => 'Bob',
    ])->renderString('<p>{greeting}</p>');

If you need to insert a rendered template fragment into another template, render the
fragment first and pass the result as a value, using the ``{! !}`` syntax so it is not
escaped again:

.. code-block:: php

    $greeting = $parser->setData(['name' => 'Bob'])->renderString('Hello, {name}!');

    $parser->setData(['greeting' => $greeting])->renderString('<p>{! greeting !}</p>');

Built-in Parser Plugin Output
=============================

The output of built-in Parser plugins is now treated as rendered data and is not
processed again as Parser syntax. This prevents values such as URLs and validation
messages from introducing variable or plugin instructions. Applications that relied
on placeholders within built-in plugin output must render those fragments explicitly.
Custom plugins that transform template code retain their existing behavior and are
responsible for keeping untrusted data separate from Parser syntax.

File Upload Validation
======================

The ``is_image``, ``mime_in``, and ``ext_in`` rules now reject client filenames
with a PHP handler extension before the final extension, including extensions
revealed by ``sanitize_filename()`` (for example, ``shell.p$hp.gif`` becomes
``shell.php.gif``). Filenames ending in dots, including dots followed by spaces,
are also rejected.

These checks apply regardless of the file's contents or the intended meaning of
its name. For example, both ``shell.php.gif`` and an innocent PHP logo named
``logo.php.gif`` fail validation, even if the latter contains only a GIF image.
Rename such files before uploading, for example to ``logo-php.gif``. Choosing a
generated filename when saving the file does not bypass these validation checks,
which run against the client filename before the file is saved.

*********************
Breaking Enhancements
*********************

****************
Behavior Changes
****************

CURLRequest Options After a Failed Request
=========================================

When ``Config\CURLRequest::$shareOptions`` is ``false``, request-specific options
are now reset even when the request throws an exception. Per-request ``baseURI``
and ``delay`` values are also reset after both successful and failed requests.
Constructor defaults remain available for subsequent requests.

If your application retries a failed request, pass its options again and reapply
any settings made with ``setAuth()``, ``setBody()``, ``setForm()``, or ``setJSON()``.
Do not rely on credentials or body data from the failed request remaining on the client.

*************
Project Files
*************

Some files in the **project space** (root, app, public, writable) received updates. Due to
these files being outside of the **system** scope they will not be changed without your intervention.

.. note:: There are some third-party CodeIgniter modules available to assist
    with merging changes to the project space:
    `Explore on Packagist <https://packagist.org/explore/?query=codeigniter4%20updates>`_.

Content Changes
===============

The following files received significant changes (including deprecations or visual adjustments)
and it is recommended that you merge the updated versions with your application:

Config
------

- app/Config/View.php
    - ``Config\View::$restrictParserConditionals`` has been added, with a default
      value set to ``false``. See :ref:`parser-restricting-conditionals` for details.

All Changes
===========

This is a list of all files in the **project space** that received changes;
many will be simple comments or formatting that have no effect on the runtime:

- app/Config/View.php
