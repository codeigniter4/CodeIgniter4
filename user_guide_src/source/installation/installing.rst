############
Installation
############

CodeIgniter4 can be installed in a number of different ways: manually,
using `Composer <https://getcomposer.org>`_, or using
`Git <https://git-scm.com/>`_. This section addresses how to use
each technique, and explains some of the pros and cons of them.

Once installed, read the next section, :doc:`running your app </installation/running>`.

.. note:: Before using CodeIgniter 4, make sure that your server meets the
          :doc:`requirements </intro/requirements>`.

Manual Installation
============================================================

The `CodeIgniter 4 framework <https://github.com/codeigniter4/framework>`_ 
repository holds the released versions of the framework.
It is intended for developers who do not wish to use Composer.

Download link: `latest version <https://github.com/CodeIgniter4/codeigniter4/releases/latest>`_.
The `user guide <https://codeigniter4.github.io/userguide/>`_ is accessible online.

Develop your app inside the ``app`` folder, and the ``public`` folder 
will be your document root. 

**Note**: This is the installation technique closest to that described 
for `CodeIgniter 3 <https://www.codeigniter.com/user_guide/installation/index.html>`_.

+------------------+---------------------------------------------------------------------------+
+ Topic            + Details                                                                   +
+==================+===========================================================================+
+ Installation     + Download the latest version, and extract it to become your project root.  +
+------------------+---------------------------------------------------------------------------+
+ Setup            + None                                                                      +
+------------------+---------------------------------------------------------------------------+
+ Upgrading        + Download a new copy of the framework, and then follow the upgrade         +
+                  + instructions to merge that with your project.                             +
+                  +                                                                           +
+                  + Typically, you replace the ``system`` folder, and check designated        +
+                  + ``app/Config`` folders for affected changes.                              +
+------------------+---------------------------------------------------------------------------+
+ Pros             + Download and run                                                          +
+------------------+---------------------------------------------------------------------------+
+ Cons             + You are responsible for merge conflicts when updating                     +
+------------------+---------------------------------------------------------------------------+
+ Structure        + app, public, system, writable                                             +
+                  +                                                                           +
+------------------+---------------------------------------------------------------------------+

Composer Installation
============================================================

The `CodeIgniter 4 framework <https://github.com/codeigniter4/framework>`_ 
repository holds the released versions of the framework.
It can be composer-installed as described here.

This installation technique would suit a developer who wishes to add
the CodeIgniter4 framework to an existing project.

The `user guide <https://codeigniter4.github.io/userguide/>`_ is accessible online.

Develop your app inside the ``app`` folder, and the ``public`` folder 
will be your document root. 

+------------------+---------------------------------------------------------------------------+
+ Topic            + Details                                                                   +
+==================+===========================================================================+
+ Installation     + In your project root, ``composer require codeigniter4/framework @alpha``  +
+------------------+---------------------------------------------------------------------------+
+ Setup            + Copy the app, public and writable folders from                            +
+                  + ``vendor/codeigniter4/framework`` to your project root                    +
+                  +                                                                           +
+                  + Copy the ``env``, ``phpunit.xml.dist`` and ``spark`` files, from          +
+                  + ``vendor/codeigniter4/framework`` to your project root                    +
+                  +                                                                           +
+                  + You will have to adjust paths to refer to vendor/codeigniter/framework``, +
+                  +                                                                           +
+                  + - the $pathsPath variable in  ``public/index.php``                        +
+                  + - the paths config in ``spark``                                           +
+                  + - the $systemDirectory variable in ``app/Config/Paths.php``               +
+------------------+---------------------------------------------------------------------------+
+ Upgrading        + ``composer update`` when there is a new release                           +
+                  +                                                                           +
+                  + Read the upgrade instructions, and check designated                       +
+                  + ``app/Config`` folders for affected changes.                              +
+------------------+---------------------------------------------------------------------------+
+ Pros             + Relatively simple installation; easy to update                            +
+------------------+---------------------------------------------------------------------------+
+ Cons             + You still need to check for ``app/Config`` changes after updating         +
+------------------+---------------------------------------------------------------------------+
+ Structure        + app, public, writable (after setup)                                       +  
+                  +                                                                           +
+                  + vendor/codeigniter4/framework/(app, public, writable (not used)           +
+                  +                                                                           +
+                  + vendor/codeigniter4/framework/system                                      +
+------------------+---------------------------------------------------------------------------+

Codebase Installation (Git)
============================================================

Install the latest version of the codebase by

- forking the `codebase  <https://github.com/codeigniter4/CodeIgniter4>`_ to your github account
- cloning your forked repository locally

This would *not* be suitable for app development, but *is* suitable
for contributing to the framework.

+------------------+------------------------------------------------------------------------------+
+ Topic            + Details                                                                      +
+==================+==============================================================================+
+ Installation     + Fork the codebase repo on ``github.com`` into your account                   +
+                  +                                                                              +
+                  + Clone your repo as a local project to work with                              +
+------------------+------------------------------------------------------------------------------+
+ Setup            + ``git remote add upstream https://github.com/codeigniter4/CodeIgniter4.git`` +
+------------------+------------------------------------------------------------------------------+
+ Upgrading        + Update your code anytime:                                                    +
+                  +                                                                              +
+                  + - ``git checkout develop``                                                   +
+                  + - ``git pull upstream develop``  and resolve merge conflicts                 +
+                  + - ``git push origin develop``                                                +
+------------------+------------------------------------------------------------------------------+
+ Pros             + - You have the latest version of the codebase (unreleased)                   +
+                  + - You can propose contributions to the framework, by creating a              +
+                  +   feature branch and submitting a pull request for it to the main repo       +
+                  + - a pre-commit hook is installed for your repo, that binds it to the         +
+                  +   coding-standard                                                            +
+------------------+------------------------------------------------------------------------------+
+ Cons             + You need to resolve merge conflicts when you synch with the repo             +
+                  +                                                                              +
+                  + You would not use this technique for app development                         +
+------------------+------------------------------------------------------------------------------+
+ Structure        + app, public, system, tests, user_guide_src, writable                         + 
+------------------+------------------------------------------------------------------------------+

App Starter Installation
============================================================

The `CodeIgniter 4 app starter <https://github.com/codeigniter4/appstarter>`_ 
repository holds a skeleton application, with a composer dependency on
the latest released version of the framework.
It can be composer-installed as described here.

This installation technique would suit a developer who wishes to start
a new CodeIgniter4 based project.

The `user guide <https://codeigniter4.github.io/userguide/>`_ is accessible online.

Develop your app inside the ``app`` folder, and the ``public`` folder 
will be your document root. 

+------------------+---------------------------------------------------------------------------+
+ Topic            + Details                                                                   +
+==================+===========================================================================+
+ Installation     + ``composer create-project codeigniter4/appstarter -s alpha PROJECT_ROOT`` +
+------------------+---------------------------------------------------------------------------+
+ Setup            + None                                                                      +
+------------------+---------------------------------------------------------------------------+
+ Upgrading        + ``composer update`` when there is a new release                           +
+                  +                                                                           +
+                  + Read the upgrade instructions, and check designated                       +
+                  + ``app/Config`` folders for affected changes.                              +
+------------------+---------------------------------------------------------------------------+
+ Pros             + Simple installation; easy to update                                       +
+------------------+---------------------------------------------------------------------------+
+ Cons             + You still need to check for ``app/Config`` changes after updating         +
+------------------+---------------------------------------------------------------------------+
+ Structure        + app, public, writable (after setup)                                       +  
+                  +                                                                           +
+                  + vendor/codeigniter4/framework/(app, public, writable (not used)           +
+                  +                                                                           +
+                  + vendor/codeigniter4/framework/system                                      +
+------------------+---------------------------------------------------------------------------+

Dev Starter Installation
============================================================

The `CodeIgniter 4 dev starter <https://github.com/codeigniter4/devstarter>`_ 
repository holds a skeleton application, with a composer dependency on
the develop branch (unreleased) of the framework.
It can be composer-installed as described here.

This installation technique would suit a developer who wishes to start
a new CodeIgniter4 based project, and who is willing to live with the
latest unreleased changes, which may be unstable.

The `development user guide <https://codeigniter4.github.io/CodeIgniter4/>`_ is accessible online.
Note that this differs from the released user guide, and will pertain to the
develop branch explicitly.

Develop your app inside the ``app`` folder, and the ``public`` folder 
will be your document root. 

+------------------+---------------------------------------------------------------------------+
+ Topic            + Details                                                                   +
+==================+===========================================================================+
+ Installation     + ``composer create-project codeigniter4/devstarter -s dev PROJECT_ROOT``   +
+------------------+---------------------------------------------------------------------------+
+ Setup            + None                                                                      +
+------------------+---------------------------------------------------------------------------+
+ Upgrading        + ``composer update`` whenever you are ready for the latest changes         +
+                  +                                                                           +
+                  + Check the changelog to see if any recent changes affect your app          +
+------------------+---------------------------------------------------------------------------+
+ Pros             + Simple installation; easy to update; bleeding edge version                +
+------------------+---------------------------------------------------------------------------+
+ Cons             + This is not guaranteed to be stable; the onus is on you to upgrade        +
+------------------+---------------------------------------------------------------------------+
+ Structure        + app, public, writable                                                     +  
+                  +                                                                           +
+                  + vendor/codeigniter4/framework/(app, public, writable (not used)           +
+                  +                                                                           +
+                  + vendor/codeigniter4/framework/system                                      +
+------------------+---------------------------------------------------------------------------+

Translations Installation
============================================================

Install the available localizations to an existing CodeIgniter 4
project with::

    composer require codeigniter4/translations @alpha

Update the translations at any time thereafter with::

    composer update

When the translations are installed, they are added to the appropriate namespace.
See the :doc:`localization page </outgoing/localization>`
for guidance.

Resulting folder structure:

    - vendor
        - codeigniter4
            - translations

Coding Standards Installation
============================================================

This is bound and installed automatically as part of the
codebase installation.

If you wish to use it inside your project too,
``composer require codeigniter4/translations @alpha``