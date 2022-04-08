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

Renaming or Relocating the Application Directory
================================================

If you would like to rename your application directory or even move
it to a different location on your server, other than your project root, open
your main **app/Config/Paths.php** and set a *full server path* in the
``$appDirectory`` variable (at about line 44):

.. literalinclude:: managing_apps/001.php

You will need to modify two additional files in your project root, so that
they can find the **Paths** configuration file:

- **/spark** runs command line apps; the path is specified on or about line 49:

  .. literalinclude:: managing_apps/002.php

- **/public/index.php** is the front controller for your webapp; the config
  path is specified on or about line 20:

  .. literalinclude:: managing_apps/003.php

Running Multiple Applications with one CodeIgniter Installation
===============================================================

If you would like to share a common CodeIgniter framework installation, to manage
several different applications, simply put all of the directories located
inside your application directory into their own (sub)-directory.

For example, let's say you want to create two applications, named **foo**
and **bar**. You could structure your application project directories like this:

.. code-block:: text

    /foo
        /app
        /public
        /tests
        /writable
        spark
    /bar
        /app
        /public
        /tests
        /writable
        spark
    /codeigniter
        /system

This would have two apps, **foo** and **bar**, both having standard application directories
and a **public** folder, and sharing a common **codeigniter** framework.

The **index.php** inside each application would refer to its own configuration,
``../app/Config/Paths.php``, and the ``$systemDirectory`` variable in **app/Config/Paths.php** inside each
of those would be set to refer to the shared common **system** folder.

If either of the applications had a command-line component, then you would also
modify **spark** inside each application's project folder, as directed above.

When you use Composer autoloader, fix the ``COMPOSER_PATH`` constant in **app/Config/Constants.php** inside each
of those:

.. literalinclude:: managing_apps/004.php
