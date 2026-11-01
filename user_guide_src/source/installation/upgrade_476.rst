#############################
Upgrading from 4.7.5 to 4.7.6
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

.. _upgrade-476-cache-delete-matching:

File Cache deleteMatching() No Longer Matches Subdirectories
============================================================

For security reasons, ``FileHandler::deleteMatching()`` now matches the pattern against
the names of files in the cache directory only. See the
`Security advisory GHSA-9hf2-g3w9-pfmp <https://github.com/codeigniter4/CodeIgniter4/security/advisories/GHSA-9hf2-g3w9-pfmp>`_
for more information.

Previously, the pattern was appended to the cache path and passed to ``glob()``, so a
pattern containing a directory separator, such as ``dir/key_*``, also deleted matching
files in a subdirectory of the cache directory. Such a pattern now never matches
anything and the method returns ``0``.

Most applications are not affected. The File handler stores every cache item directly
in the cache directory, and the default ``Config\Cache::$reservedCharacters`` forbid
``/`` and ``\`` in cache keys, so cache items could not be stored in subdirectories.
If your application places its own files in subdirectories of the cache directory and
removed them with ``deleteMatching()``, delete them directly instead, for example with
the :php:func:`delete_files()` helper function.

.. _upgrade-476-model-replace:

Model Field Protection for replace()
====================================

For security reasons, ``Model::replace()`` now applies field protection:

- With protection enabled, fields outside ``$allowedFields`` are discarded except
  for a supplied primary key. Define a non-empty list of fields callers may assign.
- Non-empty input reduced by filtering to no data or only its primary key now
  raises a ``DataException``. Review calls to ensure they supply allowed data.
- For trusted operations requiring other columns, explicitly disable protection
  with ``protect(false)`` and restore it with ``protect(true)`` afterward.

See :ref:`model-replace` for details and replacement-specific precautions.

.. _upgrade-476-named-binds:

Named Binds Are No Longer Replaced Inside Quoted Values
=======================================================

For security reasons, named bind placeholders (``:name:``) are now replaced only
where the SQL parser would see them as part of the query. A placeholder that
appears inside a quoted string (``'...'``, ``"..."``, ``$$...$$`` on PostgreSQL,
``q'[...]'`` on Oracle), inside a quoted identifier (backticks, ``"..."``, and
``[...]`` on SQL Server and SQLite), or inside a SQL comment (``--``, ``/* */``,
and ``#`` on MySQL) is now left untouched. This makes named binds behave the same
way as question mark binds, which have always ignored markers inside quoted
strings. See the
`Security advisory GHSA-25rf-99hr-7pmr <https://github.com/codeigniter4/CodeIgniter4/security/advisories/GHSA-25rf-99hr-7pmr>`_
for more information.

Previously, such placeholders were replaced as well, so a value that was already
escaped and rendered into the query (for example a subquery, a custom ``where()``
string built with ``$db->escape()``, or the ``WHERE`` binds of ``deleteBatch()``)
could be mixed with the value of another bind.

This change only concerns queries that place a placeholder inside a quoted string,
a quoted identifier, or a comment. With the default escaping, such a placeholder
never produced the intended SQL, because the quotes of the escaped value ended up
inside the literal or identifier (for example ``LIKE '%:name:%'`` compiled to
``LIKE '%'Rick'%'``). In practice, the only working queries that change their
behavior are those that disable escaping for a bind, either with the third
parameter of ``$db->query()`` or by passing a ``RawSql`` value, and rely on the
placeholder being replaced inside a quoted string, a quoted identifier, or a
comment: the placeholder is now left in the query literally. For example, the
following used to search for ``%Rick%`` and now searches for the literal string
``%:name:%``:

.. code-block:: php

    $db->query("SELECT * FROM users WHERE name LIKE '%:name:%'", ['name' => ['Rick', false]], false);

Move the quotes into the bound value instead, so that the value is escaped as a
whole:

.. code-block:: php

    $db->query('SELECT * FROM users WHERE name LIKE :name:', ['name' => '%Rick%']);

*********************
Breaking Enhancements
*********************

****************
Behavior Changes
****************

Filename Sanitization
=====================

``sanitize_filename()`` and ``Security::sanitizeFilename()`` now remove all backslashes.
Single backslashes between ordinary characters were already removed in previous versions,
so paths such as ``folder\file.txt`` produce the same result. Ordinary filenames and
relative paths with forward slashes are also unchanged. Results can differ for repeated
backslashes, literal ``\0`` sequences, and inputs where removing backslashes exposes
sequences stripped by the sanitizer.

Use forward slashes (``/``) for relative paths passed with ``$relativePath = true``,
including on Windows. If your application stores or compares sanitized filenames created
from these inputs, check for changed results and possible filename collisions.

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

- @TODO

All Changes
===========

This is a list of all files in the **project space** that received changes;
many will be simple comments or formatting that have no effect on the runtime:

- @TODO
