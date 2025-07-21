##########################
Managing your Applications
##########################

By default, it is assumed that you only intend to use CodeIgniter to
manage one application, which you will build in your **app**
directory. It is possible, however, to have multiple sets of
applications that share a single CodeIgniter installation, or even to
rename or relocate your application directory.

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

- **spark** runs command line apps.

  .. literalinclude:: managing_apps/002.php
      :lines: 2-

- **public/index.php** is the front controller for your webapp.

  .. literalinclude:: managing_apps/003.php
      :lines: 2-

.. _running-multiple-app:

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

Changing the Location of the .env File
======================================

If necessary, you can change the location of the ``.env`` file by adjusting the ``$envDirectory``
property in ``app/Config/Paths.php``.

By default, the framework loads environment settings from a ``.env`` file located one level above
the ``app/`` directory (in the ``ROOTPATH``). This is a safe location when your domain is correctly
pointed to the ``public/`` directory, as recommended.

In practice, however, some applications are served from a subdirectory (e.g., ``http://example.com/myapp``)
rather than from the main domain. In such cases, placing the ``.env`` file within the ``ROOTPATH`` may expose
sensitive configuration data if ``.htaccess`` or other protections are misconfigured.

To avoid this risk in such setups, it is recommended that you ensure the ``.env`` file is located outside any web-accessible directories.

.. warning::

   If you change the location of the ``.env`` file, make absolutely sure it is not publicly accessible.
   Exposure of this file could lead to compromised credentials and access to critical services, such as your
   database, mail server, or third-party APIs.
