#################
Official Packages
#################

The CodeIgniter framework cannot solve all of the problems that developers will encounter.
Many users have said they love how small and fast the framework is, so we don't want to
bloat the core framework. To bridge the gap we are releasing official packages to provide
additional functionality that not every site will need or want.

.. contents::
    :local:
    :depth: 2

******
Shield
******

`CodeIgniter Shield <https://github.com/codeigniter4/shield>`_ is an authentication
and authorization framework for CodeIgniter 4. It is designed to be secure, flexible,
and easily extendable to meet the needs of many different types of websites.
Among the many featues, it includes:

* Session-based authentication
* Personal access token authentication
* framework for after login/register "actions" (like Two Factor Authentication, etc)
* Role-Based Access Control with simple, flexible permissions.
* Per-user permission overrides,
* and more...

********
Settings
********

`CodeIgniter Settings <https://github.com/codeigniter4/settings>`_ is a wrapper around
the configuration files that allows any configuration setting to saved to the database,
while defaulting to the config files when not custom value has been stored. This allows
an application to ship with the default config values, but adapt as the project grows
or moves servers, without having to touch the code.
