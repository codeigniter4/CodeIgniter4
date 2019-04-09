Composer Installation
###############################################################################

.. contents::
    :local:
    :depth: 1

Composer can be used in several ways to install CodeIgniter4 on your system.

The first two techniques describe creating a skeleton project
using CodeIgniter4, that you would then use as the base for a new webapp.
The third technique described below lets you add CodeIgniter4 to an existing
webapp, 

**Note**: if you are using a Git repository to store your code, or for
collaboration with others, then the ``vendor`` folder would normally
be "git ignored". In such a case, you will need to do a ``composer udpate``
when you clone the repository to a new system.

App Starter
============================================================

The `CodeIgniter 4 app starter <https://github.com/codeigniter4/appstarter>`_ 
repository holds a skeleton application, with a composer dependency on
the latest released version of the framework.

This installation technique would suit a developer who wishes to start
a new CodeIgniter4 based project.

Installation & Setup
-------------------------------------------------------

In the folder above your project root::

    composer create-project codeigniter4/appstarter project-root -s beta

The command above will create a "project-root" folder.

If you omit the "project-root" argument, the command will create an
"appstarter" folder, which can be renamed as appropriate.

If you don't need or want phpunit installed, and all of its composer
dependencies, then add the "--no-dev" option to the end of the above
command line. That will result in only the framework, and the three
trusted dependencies that we bundle, being composer-installed.

A sample such installation command, using the default project-root "appstarter"::

    composer create-project codeigniter4/appstarter -s beta --no-dev

Upgrading
-------------------------------------------------------

Whenever there is a new release, then from the command line in your project root::

    composer update 

If you used the "--no-dev" option when you created the project, it
would be appropriate to do so here too, i.e. ``composer update --no-dev``.

Read the upgrade instructions, and check designated  ``app/Config`` folders for affected changes.

Pros
-------------------------------------------------------

Simple installation; easy to update

Cons
-------------------------------------------------------

You still need to check for ``app/Config`` changes after updating

Structure
-------------------------------------------------------

Folders in your project after setup:

- app, public, tests, writable 
- vendor/codeigniter4/framework/system
- vendor/codeigniter4/framework/app & public (compare with yours after updating)

Dev Starter
============================================================

Installation & Setup
-------------------------------------------------------

The `CodeIgniter 4 dev starter <https://github.com/codeigniter4/devstarter>`_ 
repository holds a skeleton application, just like the appstarter above,
but with a composer dependency on
the develop branch (unreleased) of the framework.
It can be composer-installed as described here.

This installation technique would suit a developer who wishes to start
a new CodeIgniter4 based project, and who is willing to live with the
latest unreleased changes, which may be unstable.

The `development user guide <https://codeigniter4.github.io/CodeIgniter4/>`_ is accessible online.
Note that this differs from the released user guide, and will pertain to the
develop branch explicitly.

In the folder above your project root::

    composer create-project codeigniter4/devstarter -s dev

The command above will create a "devstarter" folder.
Feel free to rename that for your project.

Just like the appstarter, you can provide your own project
name as the third composer argument, and you can add
the "--no-dev" argument if your don't want phpunit and its dependencies included.
An example::

    composer create-project codeigniter4/devstarter my-awesome-project -s dev --no-dev


Upgrading
-------------------------------------------------------

``composer update`` whenever you are ready for the latest changes,
or ``composer update --no-dev`` if you used that argument when creating your project.

Check the changelog to see if any recent changes affect your app,
bearing in mind that the most recent changes may not have made it
into the changelog!

Pros
-------------------------------------------------------

Simple installation; easy to update; bleeding edge version

Cons
-------------------------------------------------------

This is not guaranteed to be stable; the onus is on you to upgrade.
You still need to check for ``app/Config`` changes after updating.

Structure
-------------------------------------------------------

Folders in your project after setup:

- app, public, tests, writable 
- vendor/codeigniter4/codeigniter4/system
- vendor/codeigniter4/codeigniter4/app & public (compare with yours after updating)

Adding CodeIgniter4 to an Existing Project
============================================================

The same `CodeIgniter 4 framework <https://github.com/codeigniter4/framework>`_ 
repository described in "Manual Installation" can also be added to an
existing project using Composer.

Develop your app inside the ``app`` folder, and the ``public`` folder 
will be your document root. 

In your project root::

    composer require codeigniter4/framework @beta

As with the earlier two composer install methods, you can omit installing
phpunit and its dependencies by adding the "--no-dev" argument to the "composer require" command.

Setup
-------------------------------------------------------

Copy the app, public, tests and writable folders from ``vendor/codeigniter4/framework`` 
to your project root

Copy the ``env``, ``phpunit.xml.dist`` and ``spark`` files, from
``vendor/codeigniter4/framework`` to your project root

You will have to adjust paths to refer to vendor/codeigniter/framework``, 
- the $systemDirectory variable in ``app/Config/Paths.php``

Upgrading
-------------------------------------------------------

Whenever there is a new release, then from the command line in your project root::

    composer update 

Read the upgrade instructions, and check designated 
``app/Config`` folders for affected changes.

Pros
-------------------------------------------------------

Relatively simple installation; easy to update

Cons
-------------------------------------------------------

You still need to check for ``app/Config`` changes after updating

Structure
-------------------------------------------------------

Folders in your project after setup:

- app, public, tests, writable 
- vendor/codeigniter4/framework/system


Translations Installation
============================================================

If you want to take advantage of the system message translations,
they can be added to your project in a similar fashion. 

From the command line inside your project root::

    composer require codeigniter4/translations @beta

These will be updated along with the framework whenever you do a ``composer update``.
