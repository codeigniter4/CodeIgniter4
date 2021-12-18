Authentication 
#####################################

CodeIgniter intentionally does not provide an internal authentication or authorization class. There are a number
of great third-party modules that provide these services, as well as resources in the community for writing
your own. The following are recommended guidelines to encourage consistency among developers of
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
