################################
Working With Configuration Files
################################

Every application needs a way to define various settings that affect the application.
These are handled through configuration files. Configuration files simply
hold a class that contains its settings as public properties. Unlike in many other frameworks,
there is no single class that you need to use to access your settings. Instead, you simply
create an instance of the class and all your settings are there for you.

.. contents::
    :local:
    :depth: 2

Accessing Config Files
======================

You can access config files within your classes by creating a new instance or using the config function. All of the properties
are public, so you access the settings like any other property::

	// Creating new class by hand
	$config = new \Config\Pager();

	// Creating new class with config function
	$config = config( 'Pager', false );

	// Get shared instance with config function
	$config = config( 'Pager' );

	// Access config class with namespace
	$config = config( 'Config\\Pager' );

	// Access settings as class properties
	$pageSize = $config->perPage;

If no namespace is provided, it will look for the files in all available namespaces that have
been defined, as well as **/app/Config/**. All of the configuration files
that ship with CodeIgniter are namespaced with ``Config``. Using this namespace in your
application will provide the best performance since it knows exactly what directory to find the
files in and doesn't have to scan several locations in the filesystem to get there.

You can locate the configuration files any place on your server by using a different namespace.
This allows you to pull configuration files on the production server to a folder that is not in
the web-accessible space at all, while keeping it under **/app** for ease of access during development.

Creating Configuration Files
============================

If you need to create a new configuration file you would create a new file at your desired location,
**/app/Config** by default. Then create the class and fill it with public properties that
represent your settings::

    <?php namespace Config;

    use CodeIgniter\Config\BaseConfig;

    class App extends BaseConfig
    {
    	public $siteName  = 'My Great Site';
    	public $siteEmail = 'webmaster@example.com';

    }

The class should extend ``\CodeIgniter\Config\BaseConfig`` to ensure that it can receive environment-specific
settings.

Handling Different Environments
===============================

Because your site can operate within multiple environments, such as the developer's local machine or
the server used for the production site, you can modify your values based on the environment. Within these
you will have settings that might change depending on the server it's running on. This can include
database settings, API credentials, and other settings that will vary between deploys.

You can store values in a **.env** file in the root directory, alongside the system and application directories.
It is simply a collection of name/value pairs separated by an equal sign, much like a ".ini" file::

	S3_BUCKET="dotenv"
	SECRET_KEY="super_secret_key"

If the variable exists in the environment already, it will NOT be overwritten.

.. important:: Make sure the **.env** file is added to **.gitignore** (or your version control system's equivalent)
	so it is not checked in the code. Failure to do so could result in sensitive credentials being stored in the
	repository for anyone to find.

You are encouraged to create a template file, like **env.example**, that has all of the variables your project
needs with empty or dummy data. In each environment, you can then copy the file to **.env** and fill in the
appropriate data.

When your application runs, this file will be automatically loaded and the variables will be put into
the environment. This will work in any environment. These variables are then available through ``getenv()``,
``$_SERVER``, and ``$_ENV``. Of the three, ``getenv()`` function is recommended since it is not case-sensitive::

	$s3_bucket = getenv('S3_BUCKET');
	$s3_bucket = $_ENV['S3_BUCKET'];
	$s3_bucket = $_SERVER['S3_BUCKET'];

.. note:: If you are using Apache, then the CI_ENVIRONMENT can be set at the top of
    ``public/.htaccess``, which comes with a commented line to do that. Change the
    environment setting to the one you want to use, and uncomment that line.

Nesting Variables
=================

To save on typing, you can reuse variables that you've already specified in the file by wrapping the
variable name within ``${...}``::

	BASE_DIR="/var/webroot/project-root"
	CACHE_DIR="${BASE_DIR}/cache"
	TMP_DIR="${BASE_DIR}/tmp"

Namespaced Variables
====================

There will be times when you will have several variables with the same name. When this happens, the
system has no way of knowing what the correct value should be. You can protect against this by
"namespacing" the variables.

Namespaced variables use a dot notation to qualify variable names when those variables
get incorporated into configuration files. This is done by including a distinguishing
prefix, followed by a dot (.), and then the variable name itself::

    // not namespaced variables
    name = "George"
    db=my_db

    // namespaced variables
    address.city = "Berlin"
    address.country = "Germany"
    frontend.db = sales
    backend.db = admin
    BackEnd.db = admin

Incorporating Environment Variables Into a Configuration
========================================================

When you instantiate a configuration file, any namespaced environment variables
are considered for merging into the configuration objects' properties.

If the prefix of a namespaced variable matches the configuration class name exactly,
case-sensitive, then the trailing part of the variable name (after the dot) is
treated as a configuration property name. If it matches an existing configuration
property, the environment variable's value will override the corresponding one
in the configuration file. If there is no match, the configuration properties are left unchanged.

The same holds for a "short prefix", which is the name given to the case when the
environment variable prefix matches the configuration class name converted to lower case.

Treating Environment Variables as Arrays
========================================

A namespaced environment variable can be further treated as an array.
If the prefix matches the configuration class, then the remainder of the
environment variable name is treated as an array reference if it also
contains a dot::

    // regular namespaced variable
    SimpleConfig.name = George

    // array namespaced variables
    SimpleConfig.address.city = "Berlin"
    SimpleConfig.address.country = "Germany"

If this was referring to a SimpleConfig configuration object, the above example would be treated as::

    $address['city']    = "Berlin";
    $address['country'] = "Germany";

Any other elements of the ``$address`` property would be unchanged.

You can also use the array property name as a prefix. If the environment file
held instead::

    // array namespaced variables
    SimpleConfig.address.city = "Berlin"
    address.country = "Germany"

then the result would be the same as above.

.. _registrars:

Registrars
==========

A configuration file can also specify any number of "registrars", which are any
other classes which might provide additional configuration properties.
This is done by adding a ``registrars`` property to your configuration file,
holding an array of the names of candidate registrars.::

    protected $registrars = [
        SupportingPackageRegistrar::class
    ];

In order to act as a "registrar" the classes so identified must have a
static function named the same as the configuration class, and it should return an associative
array of property settings.

When your configuration object is instantiated, it will loop over the
designated classes in ``$registrars``. For each of these classes, which contains a method name matching
the configuration class, it will invoke that method, and incorporate any returned properties
the same way as described for namespaced variables.

A sample configuration class setup for this::

    <?php namespace App\Config;

    use CodeIgniter\Config\BaseConfig;

    class MySalesConfig extends BaseConfig
    {
        public $target        = 100;
        public $campaign      = "Winter Wonderland";
        protected $registrars = [
            '\App\Models\RegionalSales';
        ];
    }

... and the associated regional sales model might look like::

    <?php namespace App\Models;

    class RegionalSales
    {
        public static function MySalesConfig()
        {
            return ['target' => 45, 'actual' => 72];
        }
    }

With the above example, when `MySalesConfig` is instantiated, it will end up with
the two properties declared, but the value of the `$target` property will be over-ridden
by treating `RegionalSalesModel` as a "registrar". The resulting configuration properties::

    $target   = 45;
    $campaign = "Winter Wonderland";
