#############################
Upgrading from 4.2.1 to 4.3.0
#############################

Please refer to the upgrade instructions corresponding to your installation method.

- :ref:`Composer Installation App Starter Upgrading <app-starter-upgrading>`
- :ref:`Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading <adding-codeigniter4-upgrading>`
- :ref:`Manual Installation Upgrading <installing-manual-upgrading>`

.. contents::
    :local:
    :depth: 2

Mandatory File Changes
**********************

spark
=====

The following files received significant changes and
**you must merge the updated versions** with your application:

* ``spark``

.. important:: If you do not update this file, Spark commands will not work at all after running ``composer update``.

    The upgrade procedure, for example, is as follows::

        > composer update
        > cp vendor/codeigniter4/framework/spark .

Breaking Enhancements
*********************

- Since the launch of Spark Commands was extracted from ``CodeIgniter\CodeIgniter``, there may be problems running these commands if the ``Services::codeigniter()`` service has been overridden.

Project Files
*************

Numerous files in the **project space** (root, app, public, writable) received updates. Due to
these files being outside of the **system** scope they will not be changed without your intervention.
There are some third-party CodeIgniter modules available to assist with merging changes to
the project space: `Explore on Packagist <https://packagist.org/explore/?query=codeigniter4%20updates>`_.

Content Changes
===============

The following files received significant changes (including deprecations or visual adjustments)
and it is recommended that you merge the updated versions with your application:

* ``app/Config/Routes.php``
    * Due to the fact that the approach to running Spark Commands has changed, there is no longer a need to load the internal routes of the framework.

All Changes
===========

This is a list of all files in the **project space** that received changes;
many will be simple comments or formatting that have no effect on the runtime:

* app/Config/Routes.php
* spark
