############
Code Modules
############

CodeIgniter supports a form of code modularization to help you create reusable code. Modules are typically
centered around a specific subject, and can be thought of as mini-applications within your larger application. Any
of the standard file types within the framework are supported, like controllers, models, views, config files, helpers,
language files, etc. Modules may contain as few, or as many, of these as you like.

.. contents::
    :local:
    :depth: 2

==========
Namespaces
==========

The core element of the modules functionality comes from the :doc:`PSR4-compatible autoloading </concepts/autoloader>`
that CodeIgniter uses. While any code can use the PSR4 autoloader and namespaces, the primary way to take full advantage of
modules is to namespace your code and add it to **app/Config/Autoload.php**, in the ``psr4`` section.

For example, let's say we want to keep a simple blog module that we can re-use between applications. We might create
folder with our company name, Acme, to store all of our modules within. We will put it right alongside our **application**
directory in the main project root::

    /acme        // New modules directory
    /application
    /public
    /system
    /tests
    /writable

Open **app/Config/Autoload.php** and add the **Acme** namespace to the ``psr4`` array property::

    $psr4 = [
        'Config'        => APPPATH . 'Config',
        APP_NAMESPACE   => APPPATH,                // For custom namespace
        'App'           => APPPATH,                // To ensure filters, etc still found,
        'Acme'          => ROOTPATH.'acme'
    ];

Now that this is set up, we can access any file within the **acme** folder through the ``Acme`` namespace. This alone
takes care of 80% of what is needed for modules to work, so you should be sure to familiarize yourself with namespaces
and become comfortable with their use. Several file types will be scanned for automatically through all defined namespaces - a crucial ingredient for working with modules.

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

==============
Auto-Discovery
==============

Many times, you will need to specify the full namespace to files you want to include, but CodeIgniter can be
configured to make integrating modules into your applications simpler by automatically discovering many different
file types, including:

- :doc:`Events </extending/events>`
- :doc:`Registrars </general/configuration>`
- :doc:`Route files </incoming/routing>`
- :doc:`Services </concepts/services>`

This is configured in the file **app/Config/Modules.php**.

The auto-discovery system works by scanning any psr4 namespaces that have been defined within **Config/Autoload.php**
for familiar directories/files.

When at the **acme** namespace above, we would need to make one small adjustment to make it so the files could be found:
each "module" within the namespace would have to have it's own namespace defined there. **Acme** would be changed
to **Acme\Blog**. Once your module folder has been defined, the discover process would look for a Routes file, for example,
at **/acme/Blog/Config/Routes.php**, just as if it was another application.

Enable/Disable Discover
=======================

You can turn on or off all auto-discovery in the system with the **$enabled** class variable. False will disable
all discovery, optimizing performance, but negating the special capabilities of your modules.

Specify Discovery Items
=======================

With the **$activeExplorers** option, you can specify which items are automatically discovered. If the item is not
present, then no auto-discovery will happen for that item, but the others in the array will still be discovered.

Discovery and Composer
======================

Packages that were installed via Composer will also be discovered by default. This only requires that the namespace
that Composer knows about is a PSR4 namespace. PSR0 namespaces will not be detected.

If you do not want all of Composer's known directories to be scanned when locating files, you can turn this off
by editing the ``$discoverInComposer`` variable in ``Config\Modules.php``::

    public $discoverInComposer = false;

==================
Working With Files
==================

This section will take a look at each of the file types (controllers, views, language files, etc) and how they can
be used within the module. Some of this information is described in more detail in the relevant location of the user
guide, but is being reproduced here so that it's easier to grasp how all of the pieces fit together.

Routes
======

By default, :doc:`routes </incoming/routing>` are automatically scanned for within modules. It can be turned off in
the **Modules** config file, described above.

.. note:: Since the files are being included into the current scope, the ``$routes`` instance is already defined for you.
    It will cause errors if you attempt to redefine that class.

Controllers
===========

Controllers outside of the main **app/Controllers** directory cannot be automatically routed by URI detection,
but must be specified within the Routes file itself::

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

Config files are automatically discovered whenever using the **config()** function that is always available.

Migrations
==========

Migration files will be automatically discovered within defined namespaces. All migrations found across all
namespaces will be run every time.

Seeds
=====

Seed files can be used from both the CLI and called from within other seed files as long as the full namespace
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

Views can be loaded using the class namespace as described in the :doc:`views </outgoing/views>` documentation::

    echo view('Acme\Blog\Views\index');
