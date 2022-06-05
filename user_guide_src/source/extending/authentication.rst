Authentication
#####################################

CodeIgniter provides an official authentication and authorization framework
:ref:`CodeIgniter Shield <shield>` for CodeIgniter 4,
It is designed to be secure, flexible, and easily extendable to meet the needs
of many different types of websites.

The following are recommended guidelines to encourage consistency among developers of
modules, projects, and the framework itself.

Recommendations
===============

* Modules that handle login and logout operations should trigger the ``login`` and ``logout`` Events when successful
* Modules that define a "current user" should define the function ``user_id()`` to return the user's unique identifier, or ``null`` for "no current user"

Modules that fulfill these recommendations may indicate compatibility by adding the following provision to **composer.json**::

    "provide": {
        "codeigniter4/authentication-implementation": "1.0"
    },

You may view a list of modules that provide this implementation on `Packagist <https://packagist.org/providers/codeigniter4/authentication-implementation>`_.
