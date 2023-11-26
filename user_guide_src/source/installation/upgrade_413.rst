#############################
Upgrading from 4.1.2 to 4.1.3
#############################

Please refer to the upgrade instructions corresponding to your installation method.

- :ref:`Composer Installation App Starter Upgrading <app-starter-upgrading>`
- :ref:`Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading <adding-codeigniter4-upgrading>`
- :ref:`Manual Installation Upgrading <installing-manual-upgrading>`

.. contents::
    :local:
    :depth: 2

Breaking Enhancements
*********************

Cache TTL
=========

There is a new value in **app/Config/Cache.php**: ``$ttl``. This is not used by framework
handlers where 60 seconds is hard-coded, but may be useful to projects and modules.
In a future release this value will replace the hard-coded version, so either leave this as
``60`` or stop relying on the hard-coded version.

Project Files
*************

Only a few files in the project space (root, app, public, writable) received updates. Due to
these files being outside of the system scope they will not be changed without your intervention.
The following files received changes and it is recommended that you merge the updated versions with your application:

* ``app/Config/Cache.php``
* ``spark``
