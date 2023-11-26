#############################
Upgrading from 4.3.6 to 4.3.7
#############################

Please refer to the upgrade instructions corresponding to your installation method.

- :ref:`Composer Installation App Starter Upgrading <app-starter-upgrading>`
- :ref:`Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading <adding-codeigniter4-upgrading>`
- :ref:`Manual Installation Upgrading <installing-manual-upgrading>`

.. contents::
    :local:
    :depth: 2

Breaking Changes
****************

.. _upgrade-437-feature-testing:

Feature Testing Request Body
============================

If you call:

1. :ref:`withBody() <feature-setting-the-body>`
2. and :ref:`withBodyFormat() <feature-formatting-the-request>`
3. and pass the ``$params`` to :ref:`call() <feature-requesting-a-page>` (or shorthand methods)

the priority for a Request body has been changed. In the unlikely event that you
have test code affected by this change, modify it.

For example, now the ``$params`` is used to build the request body, and the ``$body``
is not used::

    $this->withBody($body)->withBodyFormat('json')->call('post', $params)

Previously, the ``$body`` was used for the request body.

Return value of Validation::loadRuleGroup()
===========================================

The return value of ``Validation::loadRuleGroup()`` has been changed from
"**rules array**" to "**array** of **rules array** and **customErrors array**"
(``[rules, customErrors]``).

If you use the method, update the code like the following::

    $rules = $this->validation->loadRuleGroup($rules);
        ↓
    [$rules, $customErrors] = $this->validation->loadRuleGroup($rules);

Project Files
*************

Some files in the **project space** (root, app, public, writable) received updates. Due to
these files being outside of the **system** scope they will not be changed without your intervention.

There are some third-party CodeIgniter modules available to assist with merging changes to
the project space: `Explore on Packagist <https://packagist.org/explore/?query=codeigniter4%20updates>`_.

Content Changes
===============

The following files received significant changes (including deprecations or visual adjustments)
and it is recommended that you merge the updated versions with your application:

Config
------

- app/Config/Kint.php

All Changes
===========

This is a list of all files in the **project space** that received changes;
many will be simple comments or formatting that have no effect on the runtime:

- app/Config/App.php
- app/Config/Autoload.php
- app/Config/Cache.php
- app/Config/ContentSecurityPolicy.php
- app/Config/Filters.php
- app/Config/Kint.php
- app/Config/Logger.php
- app/Config/Migrations.php
- app/Config/Modules.php
- app/Config/Paths.php
- app/Controllers/BaseController.php
- app/Controllers/Home.php
- composer.json
