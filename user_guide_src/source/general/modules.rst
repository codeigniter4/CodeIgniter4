############
Code Modules
############

CodeIgniter supports a very simple form of modularization to help you create reusable code. Modules are typically
centered around a specific subject, and can be thought of as mini-applications within your larger application. Any
of the standard file types within the framework are supported, like controllers, models, views, config files, helpers,
language files, etc. Modules may contain as few, or as many, of these as you like.

.. contents:: Page Contents

==========
Namespaces
==========

The core element of the modules functionality comes from the :doc:`PSR4-compatible autoloading </concepts/autoloader>`
that CodeIgniter uses. While any code can use the PSR4 autoloader and namespaces, the only way to take full advantage of
modules is to namespace your code and add it to **application/Config/Autoload.php**, in the ``psr4`` section.

For example, let's say we want to keep a simple blog module that we can re-use between components. We might create
folder with our company name, Acme, to store all of our modules within. We will put it right alongside our **application**
directory in the main project root::

    /acme        // New modules directory
    /application
    /public
    /system
    /tests
    /writable

Open **application/Config/Autoload.php** and add the **Acme** namespace to the ``psr4`` array property::

    public $psr4 = [
        'Acme' => ROOTPATH.'acme'
    ];

Now that this is setup we can access any file within the **acme** folder through the ``Acme`` namespace. This alone
takes care of 80% of what is needed for modules to work, so you should be sure to familiarize yourself within namespaces
and become comfortable with their use. A number of the file types will be scanned for automatically through all defined
namespaces here, making this crucial to working with modules at all.

A common directory structure within a module will mimic the main application folder::

    /acme
        /Blog
            /Config
            /Controllers
            /Database
                /Migrations
                /Seeds
            /Helpers
            /Language
                /en
            /Libraries
            /Models
            /Views

Of course, there is nothing forcing you to use this exact structure, and you should organize it in the manner that
best suits your module, leaving out directories you don't need, creating new directories for Entities, Interfaces,
or Repositories, etc.

==================
Working With Files
==================

This section will take a look at each of the file types (controllers, views, language files, etc) and how they can
be used within the module. Some of this information is described in more detail in the relevant location of the user
guide, but is being reproduced here so that it's easier to grasp how all of the pieces fit together.

Routes
======

By default, :doc:`routes </general/routing>` are not automatically scanned for within modules. This is to boost
performance when modules are not in use. However, it's a simple thing to scan for any Routes file within modules.
Simply change the ``discoverLocal`` setting to true in **/application/Config/Routes.php**::

    $routes->discoverLocal(true);

This will scan all PSR4 namespaced directories specified in **/application/Config/Autoload.php**. It will look for
**{namespace}/Config/Routes.php** files and load them if they exist. This way, each module can contain its own
Routes file that is kept with it whenever you add it to new projects. For our blog example, it would look for
**/acme/Blog/Config/Routes.php**.

.. note:: Since the files are being included into the current scope, the ``$routes`` instance is already defined for you.
    It will cause errors if you attempt to redefine that class.

Controllers
===========

Controllers cannot be automatically routed by URI detection, but must be specified within the Routes file itself::

    // Routes.php
    $routes->get('blog', 'Acme\Blog\Controllers\Blog::index');

To reduce the amount of typing needed here, the **group** routing feature is helpful::

    $routes->group('blog', ['namespace' => 'Acme\Blog\Controllers'], function($routes)
    {
        $routes->get('/', 'Blog::index');
    });

Config Files
============

No special change is needed when working with configuration files. These are still namespaced classes and loaded
with the ``new`` command::

    $config = new \Acme\Blog\Config\Blog();

Migrations
==========

Migration files will be automatically discovered within defined namespaces. All migrations found across all
namespaces will be run every time.

Seeds
=====

Seeds files can be used from both the CLI and called from within other seed files as long as the full namespace
is provided. If calling on the CLI, you will need to provide double backslashes::

    > php public/index.php migrations seed Acme\\Blog\\Database\\Seeds\\TestPostSeeder

Helpers
=======

Helpers will be located automatically from defined namespaces when using the ``helper()`` method, as long as it
is within the namespaces **Helpers** directory::

    helper('blog');

Language Files
==============

Language files are located automatically from defined namespaces when using the ``lang()`` method, as long as the
file follows the same directory structures as the main application directory.

Libraries
=========

Libraries are always instantiated by their fully-qualified class name, so no special access is provided::

    $lib = new \Acme\Blog\Libraries\BlogLib();

Models
======

Models are always instantiated by their fully-qualified class name, so no special access is provided::

    $model = new \Acme\Blog\Models\PostModel();

Views
=====

Views can be loaded using the class namespace as described in the :doc:`views </general/views>` documentation::

    echo view('Acme\Blog\Views\index');
