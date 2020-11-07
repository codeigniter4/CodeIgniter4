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
.. code-block:: none

	make:command <name> [options]

Argument:
=========
* ``name``: The name of the command class. **[REQUIRED]**

Options:
========
* ``--command``: The command name to run in spark. Defaults to ``command:name``.
* ``--group``: The group/namespace of the command. Defaults to ``CodeIgniter`` for basic commands, and ``Generators`` for generator commands.
* ``--type``: The type of command, whether a ``basic`` command or a ``generator`` command. Defaults to ``basic``.
* ``-n``: Set the root namespace. Defaults to value of ``APP_NAMESPACE``.
* ``--force``: Set this flag to overwrite existing files on destination.

make:controller
---------------

Creates a new controller file.

Usage:
======
.. code-block:: none

	make:controller <name> [options]

Argument:
=========
* ``name``: The name of the controller class. **[REQUIRED]**

Options:
========
* ``--bare``: Extends from ``CodeIgniter\Controller`` instead of ``BaseController``.
* ``--restful``: Extends from a RESTful resource. Choices are ``controller`` and ``presenter``. Defaults to ``controller``.
* ``-n``: Set the root namespace. Defaults to value of ``APP_NAMESPACE``.
* ``--force``: Set this flag to overwrite existing files on destination.

make:entity
-----------

Creates a new entity file.

Usage:
======
.. code-block:: none

	make:entity <name> [options]

Argument:
=========
* ``name``: The name of the entity class. **[REQUIRED]**

Options:
========
* ``-n``: Set the root namespace. Defaults to value of ``APP_NAMESPACE``.
* ``-force``: Set this flag to overwrite existing files on destination.

make:filter
-----------

Creates a new filter file.

Usage:
======
.. code-block:: none

	make:filter <name> [options]

Argument:
=========
* ``name``: The name of the filter class. **[REQUIRED]**

Options:
========
* ``-n``: Set the root namespace. Defaults to value of ``APP_NAMESPACE``.
* ``--force``: Set this flag to overwrite existing files on destination.

make:model
----------

Creates a new model file.

Usage:
======
.. code-block:: none

	make:model <name> [options]

Argument:
=========
* ``name``: The name of the model class. **[REQUIRED]**

Options:
========
* ``--dbgroup``: Database group to use. Defaults to ``default``.
* ``--entity``: Set this flag to use an entity class as the return type.
* ``--table``: Supply a different table name. Defaults to the pluralized class name.
* ``-n``: Set the root namespace. Defaults to value of ``APP_NAMESPACE``.
* ``--force``: Set this flag to overwrite existing files on destination.

make:seeder
-----------

Creates a new seeder file.

Usage:
======
.. code-block:: none

	make:seeder <name> [options]

Argument:
=========
* ``name``: The name of the seeder class. **[REQUIRED]**

Options:
========
* ``-n``: Set the root namespace. Defaults to value of ``APP_NAMESPACE``.
* ``--force``: Set this flag to overwrite existing files on destination.

make:migration
--------------

Creates a new migration file.

Usage:
======
.. code-block:: none

	make:migration <name> [options]

Argument:
=========
* ``name``: The name of the migration class. **[REQUIRED]**

Options:
========
* ``-n``: Set the root namespace. Defaults to value of ``APP_NAMESPACE``.
* ``--force``: Set this flag to overwrite existing files on destination.

session:migration
-----------------

Generates the migration file for database sessions.

Usage:
======
.. code-block:: none

	session:migration [options]

Options:
========
* ``-g``: Set the database group.
* ``-t``: Set the table name. Defaults to ``ci_sessions``.
* ``-n``: Set the root namespace. Defaults to value of ``APP_NAMESPACE``.
* ``--force``: Set this flag to overwrite existing files on destination.

.. note:: When running ``php spark help session:migration``, you will see that it has the argument ``name`` listed.
	This argument is not used as the class name is derived from the table name passed to the ``-t`` option.

.. note:: Do you need to have the generated code in a subfolder? Let's say if you want to create a controller
	class to reside in the ``Admin`` subfolder of the main ``Controllers`` folder, you will just need
	to prepend the subfolder to the class name, like this: ``php spark make:controller admin/login``. This
	command will create the ``Login`` controller in the ``Controllers/Admin`` subfolder with
	a namespace of ``App\Controllers\Admin``.

.. note:: Working on modules? Code generation will set the root namespace to a default of ``APP_NAMESPACE``.
	Should you need to have the generated code elsewhere in your module namespace, make sure to set
	the ``-n`` option in your command, e.g. ``php spark make:model blog -n Acme\Blog``.

.. warning:: Make sure when setting the ``-n`` option that the supplied namespace is a valid namespace
	defined in your ``$psr4`` array in ``Config\Autoload`` or defined in your composer autoload file.
	Otherwise, a ``RuntimeException`` will be thrown.

.. warning:: Use of ``migrate:create`` to create migration files is now deprecated. It will be removed in
	future releases. Please use ``make:migration`` as replacement.

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
(3) ``App\Entities\User``;
(4) ``App\Database\Migrations\<some date here>_User``; and
(5) ``App\Database\Seeds\User``.

****************
GeneratorCommand
****************

All generator commands must extend ``GeneratorCommand`` to fully utilize its methods that are used in code
generation. While some of the methods are already functional, you may have the need to customize or upgrade
what each method does. You can do so as all methods have protected visibility, except for the ``run()`` method
which is public and need not be overridden as it is essentially complete.

.. php:class:: CodeIgniter\\CLI\\GeneratorCommand

	.. php:method:: getClassName()

		:rtype: string

		Gets the class name from input. This can be overridden if name is really
		required by providing a prompt.

	.. php:method:: sanitizeClassName(string $class)

		:param string $class: Class name.
		:rtype: string

		Trims input, normalize separators, and ensures all paths are in Pascal case.

	.. php:method:: qualifyClassName(string $class)

		:param string $class: Class name.
		:rtype: string

		Parses the class name and checks if it is already fully qualified.

	.. php:method:: getRootNamespace()

		:rtype: string

		Gets the root namespace from input. Defaults to value of ``APP_NAMESPACE``.

	.. php:method:: getNamespacedClass(string $rootNamespace, string $class)

		:param string $rootNamespace: The root namespace of the class.
		:param string $class: Class name
		:returns: The fully qualified class name
		:rtype: string

		Gets the qualified class name. This should be implemented.

	.. php:method:: buildPath(string $class)

		:param string $class: The fully qualified class name
		:returns: The absolute path to where the class will be saved.
		:rtype: string
		:throws: RuntimeException

		Builds the file path from the class name.

	.. php:method:: modifyBasename(string $filename)

		:param string $filename: The basename of the file path.
		:returns: A modified basename for the file.
		:rtype: string

		Provides last chance for child generators to change the file's basename before saving.
		This is useful for migration files where the basename has a date component.

	.. php:method:: buildClassContents(string $class)

		:param string $class: The fully qualified class name.
		:rtype: string

		Builds the contents for class being generated, doing all the replacements necessary in the template.

	.. php:method:: getTemplate()

		:rtype: string

		Gets the template for the class being generated. This must be implemented.

	.. php:method:: getNamespace(string $class)

		:param string $class: The fully qualified class name.
		:rtype: string

		Retrieves the namespace part from the fully qualified class name.

	.. php:method:: setReplacements(string $template, string $class)

		:param string $template: The template string to use.
		:param string $class: The fully qualified class name.
		:returns: The template string with all annotations replaced.
		:rtype: string

		Performs all the necessary replacements.

	.. php:method:: sortImports(string $template)

		:param string $template: The template file.
		:returns: The template file with all imports already sorted.
		:rtype: string

		Alphabetically sorts the imports for a given template.

.. warning:: Child generators should make sure to implement ``GeneratorCommand``'s two abstract methods:
	``getNamespacedClass`` and ``getTemplate``, or else you will get a PHP fatal error.

.. note:: ``GeneratorCommand`` has the default argument of ``['name' => 'Class name']``. You can
	override the description by supplying the name in your ``$arguments`` property, e.g. ``['name' => 'Module class name']``.

.. note:: ``GeneratorCommand`` has the default options of ``-n`` and ``--force``. Child classes cannot override
	these two properties as they are crucial in the implementation of the code generation.

.. note:: Generators are default listed under the ``Generators`` namespace because it is the default group
	name in ``GeneratorCommand``. If you want to have your own generator listed elsewhere under a different
	namespace, you will just need to provide the ``$group`` property in your child generator,
	e.g. ``protected $group = 'CodeIgniter';``.
