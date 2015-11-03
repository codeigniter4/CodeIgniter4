################################
Working With Configuration Files
################################

Every application needs a way to define various settings that affect the various parts of
the application. These are handled through configuration files. Configuration files simply
hold a class that contains it's settings as public properties. Unlike in many other frameworks,
there is not a single class that you need to use to access your settings. Instead, you simply
create an instance of the class and all your settings are there for you.

Accessing Config Files
======================

You can access config files within your files by creating a new instance. All of the properties
are public, so you access the settings like any other property::

	$config = new EmailConfig();
	
	// Access settings as class properties
	$protocol = $config->protocol;
	$mailpath = $config->mailpath;

If no namespace is provided, it will look for the files in all available namespaces that have
been defined, as well as the traditional ``/application/config/``. All of the configuration files
that ship with CodeIgniter are namespaced with ``App\Config``. Using this namespace in your
application will provide the best performance since it knows exactly what folder to find the
files in and doesn't have to scan several locations in the filesystem to get there.

You can locate the configuration files any place on your server by using a different namespace.
This allows you to pull configuration files on the production server to a folder that is not in
the web server space at all, while keeping it under ``/application`` for ease of access during development.

Creating Configuration Files
============================

If you need to create a new configuration file you would create a new simple class. Create a new
file at your desired location, ``/application/config`` by default. Then create the class and fill it
with public properties that represent your settings::

	<?php namespace App\Config;
	
	class AppConfig extends \CodeIgniter\Config\BaseConfig {
	
		public $siteName = 'My Great Site';
		public $siteEmail = 'webmaster@example.com';
		
	}
	
Handling Different Environments
===============================

Because your site can operate within multiple different environments, you can modify your values based
on the environment. Within these environments you will have settings that might change per the environment.
This can include database settings, API credentials, and other settings that will vary between deploys.

You can store values in a ``.env`` file in the ``/application`` directory. This file should NOT be checked into
any type of version control systems. The file is simply a collection of name/value pairs separated by an equal
sign, much like a .ini file. **Make sure the .env file is added to your .gitignore so it is not checked-in the code**::

	S3_BUCKET="dotenv"
	SECRET_KEY="super_secret_key"

If the variable exists in the environment already, it will NOT be overwritten. 

You are encouraged to create a template file, like ``env.example``, that has all of the variables your project
needs with empty or dummy data. In each environment, you can then copy the file to ``.env`` and fill in the
appropriate data.

When your application runs, this file will be automatically loaded and the variables will be put into
the environment. This will work in any environment except for production, where the variables should be
set in the environment through whatever means your server supports, such as .htaccess files, etc. These
variables are then available through ``getenv()``, ``$_SERVER``, and ``$_ENV``. Of the three, ``getenv()`` function
is recommended since it is not case-sensitive::

	$s3_bucket = getenv('S3_BUCKET');
	$s3_bucket = $_ENV['S3_BUCKET'];
	$s3_bucket = $_SERVER['S3_BUCKET'];

Nesting Variables
=================

To save on typing, you can reuse variables that you've already specified in the file by wrapping the
variable name within ``${...}``::

	BASE_DIR="/var/webroot/project-root"
	CACHE_DIR="${BASE_DIR}/cache"
	TMP_DIR="${BASE_DIR}/tmp" 


