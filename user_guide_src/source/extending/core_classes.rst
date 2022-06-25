****************************
Creating Core System Classes
****************************

Every time CodeIgniter runs there are several base classes that are initialized automatically as part of the core
framework. It is possible, however, to swap any of the core system classes with your own version or even just extend
the core versions.

**Most users will never have any need to do this, but the option to replace or extend them does exist for those
who would like to significantly alter the CodeIgniter core.**

.. important:: Messing with a core system class has a lot of implications, so make sure you know what you are doing before
    attempting it.

.. contents::
    :local:
    :depth: 2

System Class List
=================

The following is a list of the core system classes that are invoked every time CodeIgniter runs:

*  ``CodeIgniter\Autoloader\Autoloader``
*  ``CodeIgniter\CodeIgniter``
*  ``CodeIgniter\Config\DotEnv``
*  ``CodeIgniter\Config\Services``
*  ``CodeIgniter\Controller``
*  ``CodeIgniter\Debug\Exceptions``
*  ``CodeIgniter\Debug\Timer``
*  ``CodeIgniter\Events\Events``
*  ``CodeIgniter\Filters\Filters``
*  ``CodeIgniter\HTTP\ContentSecurityPolicy``
*  ``CodeIgniter\HTTP\CLIRequest`` (if launched from command line only)
*  ``CodeIgniter\HTTP\IncomingRequest`` (if launched over HTTP)
*  ``CodeIgniter\HTTP\Request``
*  ``CodeIgniter\HTTP\Response``
*  ``CodeIgniter\HTTP\Message``
*  ``CodeIgniter\HTTP\URI``
*  ``CodeIgniter\Log\Logger``
*  ``CodeIgniter\Log\Handlers\BaseHandler``
*  ``CodeIgniter\Log\Handlers\FileHandler``
*  ``CodeIgniter\Router\RouteCollection``
*  ``CodeIgniter\Router\Router``
*  ``CodeIgniter\View\View``

Replacing Core Classes
======================

To use one of your own system classes instead of a default one, ensure:

    1. the :doc:`Autoloader <../concepts/autoloader>` can find your class,
    2. your new class implements the appropriate interface,
    3. and modify the appropriate :doc:`Service <../concepts/services>` to load your class in place of the core class.

Creating Your Class
-------------------

For example, if you have a new ``App\Libraries\RouteCollection`` class that you would like to use in place of
the core system class, you would create your class like this:

.. literalinclude:: core_classes/001.php

Adding the Service
------------------

Then you would add the ``routes`` service in **app/Config/Services.php** to load your class instead:

.. literalinclude:: core_classes/002.php

Extending Core Classes
======================

If all you need to is add some functionality to an existing library - perhaps add a method or two - then it's overkill
to recreate the entire library. In this case, it's better to simply extend the class. Extending the class is nearly
identical to `Replacing Core Classes`_ with one exception:

* The class declaration must extend the parent class.

For example, to extend the native ``RouteCollection`` class, you would declare your class with:

.. literalinclude:: core_classes/003.php

If you need to use a constructor in your class make sure you extend the parent constructor:

.. literalinclude:: core_classes/004.php

**Tip:**  Any functions in your class that are named identically to the methods in the parent class will be used
instead of the native ones (this is known as "method overriding"). This allows you to substantially alter the CodeIgniter core.
