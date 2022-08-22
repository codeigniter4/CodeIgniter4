##########################
Managing your Applications
##########################

By default, it is assumed that you only intend to use CodeIgniter to
manage one application, which you will build in your **app**
directory. It is possible, however, to have multiple sets of
applications that share a single CodeIgniter installation, or even to
rename or relocate your application directory.

.. important:: When you installed CodeIgniter v4.1.9 or before, and if there are ``App\\`` and ``Config\\`` namespaces in your ``/composer.json``'s ``autoload.psr-4`` like the following, you need to remove these lines, and run ``composer dump-autoload``.

    .. code-block:: text

        {
            ...
            "autoload": {
                "psr-4": {
                    "App\\": "app",             <-- Remove this line
                    "Config\\": "app/Config"    <-- Remove this line
                }
            },
            ...
        }

.. contents::
    :local:
    :depth: 2

.. _renaming-app-directory:

Renaming or Relocating the Application Directory
================================================

If you would like to rename your application directory or even move
it to a different location on your server, other than your project root, open
your main **app/Config/Paths.php** and set a *full server path* in the
``$appDirectory`` variable (at about line 44):

.. literalinclude:: managing_apps/001.php

You will need to modify two additional files in your project root, so that
they can find the **Paths** configuration file:

- **/spark** runs command line apps.

  .. literalinclude:: managing_apps/002.php

- **/public/index.php** is the front controller for your webapp.

  .. literalinclude:: managing_apps/003.php

Running Multiple Applications with one CodeIgniter Installation
===============================================================

If you would like to share a common CodeIgniter framework installation, to manage
several different applications, simply put all of the directories located
inside your application directory into their own (sub)-directory.

For example, let's say you want to create two applications, named **foo**
and **bar**. You could structure your application project directories like this:

.. code-block:: text

    foo/
        app/
        public/
        tests/
        writable/
        env
        phpunit.xml.dist
        spark
    bar/
        app/
        public/
        tests/
        writable/
        env
        phpunit.xml.dist
        spark
    vendor/
        autoload.php
        codeigniter4/framework/
    composer.json
    composer.lock

.. note:: If you install CodeIgniter from the Zip file, the directory structure would be following:

    .. code-block:: text

        foo/
        bar/
        codeigniter4/system/

This would have two apps, **foo** and **bar**, both having standard application directories
and a **public** folder, and sharing a common **codeigniter4/framework**.

The ``$systemDirectory`` variable in **app/Config/Paths.php** inside each
of those would be set to refer to the shared common **codeigniter4/framework** folder:

.. literalinclude:: managing_apps/005.php

.. note:: If you install CodeIgniter from the Zip file, the ``$systemDirectory`` would be ``__DIR__ . '/../../../codeigniter4/system'``.

And modify the ``COMPOSER_PATH`` constant in **app/Config/Constants.php** inside each
of those:

.. literalinclude:: managing_apps/004.php

Only when you change the Application Directory, see :ref:`renaming-app-directory` and modify the paths in the **index.php** and **spark**.
