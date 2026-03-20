#############################
Upgrading from 4.7.x to 4.8.0
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

*********************
Breaking Enhancements
*********************

Log Handler Interface
=====================

``CodeIgniter\Log\Handlers\HandlerInterface::handle()`` now accepts a third
parameter ``array $context = []``.

If you have a custom log handler that overrides the ``handle()`` method
(whether implementing ``HandlerInterface`` directly or extending a built-in
handler class), you must update your ``handle()`` method signature:

.. code-block:: php

    // Before
    public function handle($level, $message): bool

    // After
    public function handle($level, $message, array $context = []): bool

The context array may contain the CI global context data under the
``HandlerInterface::GLOBAL_CONTEXT_KEY`` (``'_ci_context'``) key when
``$logGlobalContext`` is enabled in ``Config\Logger``.

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

- app/Config/Mimes.php
    - ``Config\Mimes::$mimes`` added a new key ``md`` for Markdown files.

All Changes
===========

This is a list of all files in the **project space** that received changes;
many will be simple comments or formatting that have no effect on the runtime:

- @TODO
