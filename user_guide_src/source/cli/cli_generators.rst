##############
CLI Generators
##############

CodeIgniter4 now comes equipped with generators to ease the creation of stock controllers, models, entities,
etc. You can also scaffold a complete set of files with just one command.

.. contents::
    :local:
    :depth: 2

************
Introduction
************

All built-in generators reside under the ``Generators`` namespace when listed using ``php spark list``.
To view the full description and usage information on a particular generator, use the command::

    > php spark help <generator_command>

where ``<generator_command>`` will be replaced with the command to check.

*******************
Built-in Generators
*******************

CodeIgniter4 ships the following generators by default.

make:command
------------

Creates a new spark command.

Usage:
======
::

    make:command <name> [options]

Argument:
=========
* ``name``: The name of the command class. **[REQUIRED]**

Options:
========
* ``--command``: The command name to run in spark. Defaults to ``command:name``.
* ``--group``: The group/namespace of the command. Defaults to ``CodeIgniter`` for basic commands, and ``Generators`` for generator commands.
* ``--type``: The type of command, whether a ``basic`` command or a ``generator`` command. Defaults to ``basic``.
* ``--namespace``: Set the root namespace. Defaults to value of ``APP_NAMESPACE``.
* ``--suffix``: Append the component suffix to the generated class name.
* ``--force``: Set this flag to overwrite existing files on destination.

make:config
-----------

Creates a new config file.

Usage:
======
::

    make:config <name> [options]

Argument:
=========
* ``name``: The name of the config class. **[REQUIRED]**

Options:
========
* ``--namespace``: Set the root namespace. Defaults to value of ``APP_NAMESPACE``.
* ``--suffix``: Append the component suffix to the generated class name.
* ``--force``: Set this flag to overwrite existing files on destination.

make:controller
---------------

Creates a new controller file.

Usage:
======
::

    make:controller <name> [options]

Argument:
=========
* ``name``: The name of the controller class. **[REQUIRED]**

Options:
========
* ``--bare``: Extends from ``CodeIgniter\Controller`` instead of ``BaseController``.
* ``--restful``: Extends from a RESTful resource. Choices are ``controller`` and ``presenter``. Defaults to ``controller``.
* ``--namespace``: Set the root namespace. Defaults to value of ``APP_NAMESPACE``.
* ``--suffix``: Append the component suffix to the generated class name.
* ``--force``: Set this flag to overwrite existing files on destination.

make:entity
-----------

Creates a new entity file.

Usage:
======
::

    make:entity <name> [options]

Argument:
=========
* ``name``: The name of the entity class. **[REQUIRED]**

Options:
========
* ``--namespace``: Set the root namespace. Defaults to value of ``APP_NAMESPACE``.
* ``--suffix``: Append the component suffix to the generated class name.
* ``--force``: Set this flag to overwrite existing files on destination.

make:filter
-----------

Creates a new filter file.

Usage:
======
::

    make:filter <name> [options]

Argument:
=========
* ``name``: The name of the filter class. **[REQUIRED]**

Options:
========
* ``--namespace``: Set the root namespace. Defaults to value of ``APP_NAMESPACE``.
* ``--suffix``: Append the component suffix to the generated class name.
* ``--force``: Set this flag to overwrite existing files on destination.

make:model
----------

Creates a new model file.

Usage:
======
::

    make:model <name> [options]

Argument:
=========
* ``name``: The name of the model class. **[REQUIRED]**

Options:
========
* ``--dbgroup``: Database group to use. Defaults to ``default``.
* ``--return``: Set the return type from ``array``, ``object``, or ``entity``. Defaults to ``array``.
* ``--table``: Supply a different table name. Defaults to the pluralized class name.
* ``--namespace``: Set the root namespace. Defaults to value of ``APP_NAMESPACE``.
* ``--suffix``: Append the component suffix to the generated class name.
* ``--force``: Set this flag to overwrite existing files on destination.

make:seeder
-----------

Creates a new seeder file.

Usage:
======
::

    make:seeder <name> [options]

Argument:
=========
* ``name``: The name of the seeder class. **[REQUIRED]**

Options:
========
* ``--namespace``: Set the root namespace. Defaults to value of ``APP_NAMESPACE``.
* ``--suffix``: Append the component suffix to the generated class name.
* ``--force``: Set this flag to overwrite existing files on destination.

make:migration
--------------

Creates a new migration file.

Usage:
======
::

    make:migration <name> [options]

Argument:
=========
* ``name``: The name of the migration class. **[REQUIRED]**

Options:
========
* ``--session``: Generate a migration file for database sessions.
* ``--table``: Set the table name to use for database sessions. Defaults to ``ci_sessions``.
* ``--dbgroup``: Set the database group for database sessions. Defaults to ``default`` group.
* ``--namespace``: Set the root namespace. Defaults to value of ``APP_NAMESPACE``.
* ``--suffix``: Append the component suffix to the generated class name.
* ``--force``: Set this flag to overwrite existing files on destination.

.. note:: Do you need to have the generated code in a subfolder? Let's say if you want to create a controller
    class to reside in the ``Admin`` subfolder of the main ``Controllers`` folder, you will just need
    to prepend the subfolder to the class name, like this: ``php spark make:controller admin/login``. This
    command will create the ``Login`` controller in the ``Controllers/Admin`` subfolder with
    a namespace of ``App\Controllers\Admin``.

.. note:: Working on modules? Code generation will set the root namespace to a default of ``APP_NAMESPACE``.
    Should you need to have the generated code elsewhere in your module namespace, make sure to set
    the ``--namespace`` option in your command, e.g., ``php spark make:model blog --namespace Acme\Blog``.

.. warning:: Make sure when setting the ``--namespace`` option that the supplied namespace is a valid
    namespace defined in your ``$psr4`` array in ``Config\Autoload`` or defined in your composer autoload
    file. Otherwise, code generation will be interrupted.

.. warning:: Use of ``migrate:create`` to create migration files is now deprecated. It will be removed in
    future releases. Please use ``make:migration`` as replacement. Also, please use ``make:migration --session``
    to use instead of the deprecated ``session:migration``.

****************************************
Scaffolding a Complete Set of Stock Code
****************************************

Sometimes in our development phase we are creating functionalities by groups, such as creating an *Admin* group.
This group will contain its own controller, model, migration files, or even entities. You may be tempted to type
each generator command one-by-one in the terminal and wishfully thinking it would be great to have a single generator
command to rule them all.

Fret no more! CodeIgniter4 is also shipped with a dedicated ``make:scaffold`` command that is basically a
wrapper to the controller, model, entity, migration, and seeder generator commands. All you need is the class
name that will be used to name all the generated classes. Also, **individual options** supported by each
generator command are recognized by the scaffold command.

Running this in your terminal::

    php spark make:scaffold user

will create the following classes:

(1) ``App\Controllers\User``;
(2) ``App\Models\User``;
(3) ``App\Database\Migrations\<some date here>_User``; and
(4) ``App\Database\Seeds\User``.

To include an ``Entity`` class in the scaffolded files, just include the ``--return entity`` to the command
and it will be passed to the model generator.

**************
GeneratorTrait
**************

All generator commands must use the ``GeneratorTrait`` to fully utilize its methods that are used in code
generation.
