Upgrade Configuration
#####################

.. contents::
    :local:
    :depth: 2

Documentations
==============

- `Config Documentation CodeIgniter 3.X <http://codeigniter.com/userguide3/libraries/config.html>`_
- :doc:`Configuration Documentation CodeIgniter 4.X </general/configuration>`

What has been changed
=====================

- In CI4, the configurations are now stored in classes which extend ``CodeIgniter\Config\BaseConfig``.
- The **application/config/config.php** in CI3 will be **app/Config/App.php**
  and some other files like **app/Config/Security.php** for the specific classes.
- Within the configuration class, the config values are stored in public class properties.
- The method to fetch config values has been changed.

Upgrade Guide
=============

1. You have to change the values in the default CI4 config files according to the
   changes in the CI3 files. The config names are pretty much the same as in CI3.
2. If you are using custom config files in your CI3 project you have to create those
   files as new PHP classes in your CI4 project in **app/Config**. These classes
   should be in the ``Config`` namespace and should extend ``CodeIgniter\Config\BaseConfig``.
3. Once you have created all custom config classes, you have to copy the variables
   from the CI3 config into the new CI4 config class as public class properties.
4. Now, you have to change the config fetching syntax everywhere you fetch config
   values. The CI3 syntax is something like ``$this->config->item('item_name');``.
   You have to change this into ``config('MyConfig')->item_name;``.

Code Example
============

CodeIgniter Version 3.x
------------------------

Path: **application/config/site.php**:

.. literalinclude:: upgrade_configuration/ci3sample/001.php

CodeIgniter Version 4.x
-----------------------

Path: **app/Config/Site.php**:

.. literalinclude:: upgrade_configuration/001.php
