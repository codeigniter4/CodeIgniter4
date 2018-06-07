########################
Testing: Getting Started
########################

This page will help you ensure that you have phpUnit installed and ready to go, as well as showing you the basics
of testing with CodeIgniter.

.. contents::
    :local:
    :depth: 2

************
System Setup
************

Installing phpUnit
==================

CodeIgniter uses `phpUnit <https://phpunit.de/>`_ as the basis for all of its testing. There are two ways to install
phpUnit to use within your system.

Composer
--------

The recommended method is to install it in your project using `Composer <https://getcomposer.org/>`_. While it's possible
to install it globally we do not recommend it, since it can cause compatibility issues with other projects on your
system as time goes on.

Ensure that you have Composer installed on your system. From the project root (the directory that contains the
application and system directories) type the followin from the command line::

    > composer require --dev phpunit/phpunit

This will install the correct version for your current PHP version. Once that is done, you can run all of the
tests for this project by typing::

    > ./vendor/bin/phpunit

Phar
----

The other option is to download the .phar file from the `phpUnit <https://phpunit.de/getting-started/phpunit-7.html>`_ site.
This is standalone file that should be placed within your project root.


phpunit.xml
===========

A basic configuration for phpUnit has already been defined in ``phpunit.xml.dist``. Any project-specific changes to
this file should be done in a separate file so that it doesn't get overwritten by any framework updates. Copying the file
to ``phpunit.xml`` will create a file that phpUnit will use instead of the original file.

