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
