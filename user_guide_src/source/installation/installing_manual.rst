Manual Installation
###############################################################################

The `CodeIgniter 4 framework <https://github.com/codeigniter4/framework>`_
repository holds the released versions of the framework.
It is intended for developers who do not wish to use Composer.

Develop your app inside the ``app`` folder, and the ``public`` folder
will be your public-facing document root. Do not change anything inside the ``system``
folder!

**Note**: This is the installation technique closest to that described
for `CodeIgniter 3 <https://codeigniter.com/user_guide/installation/index.html>`_.

Installation
============================================================

Download the `latest version <https://github.com/CodeIgniter4/framework/releases/latest>`_,
and extract it to become your project root.

Setting Up
-------------------------------------------------------

None

Upgrading
-------------------------------------------------------

Download a new copy of the framework, and then follow the upgrade
instructions in the release notice or changelog to merge that with your project.

Typically, you replace the ``system`` folder, and check designated
``app/Config`` folders for affected changes.

Pros
-------------------------------------------------------

Download and run

Cons
-------------------------------------------------------

You are responsible for merge conflicts when updating

Structure
-------------------------------------------------------

Folders in your project after set up:
app, public, system, writable


Translations Installation
============================================================

If you want to take advantage of the system message translations,
they can be added to your project in a similar fashion.

Download the `latest version of them <https://github.com/codeigniter4/translations/releases/latest>`_.
Extract the downloaded zip, and copy the ``Language`` folder contents in it
to your ``PROJECT_ROOT/app/Languages`` folder.

This would need to be repeated to incorporate any updates
to the translations.
