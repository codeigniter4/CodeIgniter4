Upgrade Configuration
#####################

.. contents::
    :local:
    :depth: 1

Documentations
==============

- `Config Documentation CodeIgniter 3.X <http://codeigniter.com/userguide3/libraries/config.html>`_
- :doc:`Configuration Documentation CodeIgniter 4.X </general/configuration>`


What has been changed
=====================

- In CI4, the configurations are now stored in classes which extend ``CodeIgniter\Config\BaseConfig``.
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
   You have to change this into ``config('MyConfigFile')->item_name;``.

Code Example
============

CodeIgniter Version 3.11
------------------------

Path: **application/config**::

    <?php

    defined('BASEPATH') OR exit('No direct script access allowed');

    $siteName  = 'My Great Site';
    $siteEmail = 'webmaster@example.com';

CodeIgniter Version 4.x
-----------------------

Path: **app/Config**::

   <?php

   namespace Config;

   use CodeIgniter\Config\BaseConfig;

   class CustomClass extends BaseConfig
   {
      public $siteName  = 'My Great Site';
      public $siteEmail = 'webmaster@example.com';
   }
