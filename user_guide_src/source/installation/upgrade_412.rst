#############################
Upgrading from 4.1.1 to 4.1.2
#############################

**current_url() and indexPage**

Due to `a bug <https://github.com/codeigniter4/CodeIgniter4/issues/4116>`_ in ``current_url()``,
the resulting URIs could be incorrect for a project's configuration, most importantly: ``indexPage``
would *not* be included. Projects using ``App::$indexPage`` should expect altered values from
``current_url()`` and all its dependencies (including Response Testing, Pager, Form Helper, Pager,
and View Parser). Update your projects accordingly.

**Cache Keys**

Cache handlers had wildly different compatibility for keys. The updated cache drivers now pass
all keys through validation, roughly matching PSR-6's recommendations:

	A string of at least one character that uniquely identifies a cached item. Implementing libraries
	MUST support keys consisting of the characters A-Z, a-z, 0-9, _, and . in any order in UTF-8 encoding
	and a length of up to 64 characters. Implementing libraries MAY support additional characters and
	encodings or longer lengths, but must support at least that minimum. Libraries are responsible for
	their own escaping of key strings as appropriate, but MUST be able to return the original unmodified
	key string. The following characters are reserved for future extensions and MUST NOT be supported by
	implementing libraries: ``{}()/\@:``

Update your projects to remove any invalid cache keys.

**BaseConnection::query() return values**

``BaseConnection::query()`` method in prior versions was incorrectly returning BaseResult objects
even if the query failed. This method will now return ``false`` for failed queries (or throw an
Exception if ``DBDebug==true``) and will return booleans for write-type queries. Review any use
of ``query()`` method and be assess whether the value might be boolean instead of Result object.
For a better idea of what queries are write-type queries, check ``BaseConnection::isWriteType()``
and any DBMS-specific override ``isWriteType()`` in the relevant Connection class.

**ConnectionInterface::isWriteType() declaration added**

If you have written any classes that implement ConnectionInterface, these must now implement the
``isWriteType()`` method, declared as ``public function isWriteType($sql): bool``. If your class extends BaseConnection, then that class will provide a basic ``isWriteType()``
method which you might want to override.

**Test Traits**

The ``CodeIgniter\Test`` namespace has had significant improvements to help developers with their
own test cases. Most notably test extensions have moved to Traits to make them easier to
pick-and-choose across various test case needs. The ``DatabaseTestCase`` and ``FeatureTestCase``
classes have been deprecated and their methods moved to ``DatabaseTestTrait`` and
``FeatureTestTrait`` respectively. Update your test cases to extend the main test case
and use any traits you need. For example::

    use CodeIgniter\Test\DatabaseTestCase;

    class MyDatabaseTest extends DatabaseTestCase
    {
        public function testBadRow()
        {

... becomes::

    use CodeIgniter\Test\CIUnitTestCase;
    use CodeIgniter\Test\DatabaseTestTrait;

    class MyDatabaseTest extends CIUnitTestCase
    {
        use DatabaseTestTrait;

        public function testBadRow()
        {

Finally, ``ControllerTester`` has been superseded by ``ControllerTestTrait`` to standardize
approach and take advantage of the updated response testing (below).

**Test Responses**

The tools for testing responses have been consolidated and improved. A new
``TestResponse`` replaces ``ControllerResponse`` and ``FeatureResponse`` with a complete
set of methods and properties expected from both those classes. In most cases these changes
will be "behind the scenes" by ``ControllerTestTrait`` and ``FeatureTestCase``, but two
changes to be aware of:

* ``TestResponse``'s ``$request`` and ``$response`` properties are protected and should only be access through their getter methods, ``request()`` and ``response()``
* ``TestResponse`` does not have ``getBody()`` and ``setBody()`` methods, but rather uses the Response methods directly, e.g.: ``$body = $result->response()->getBody();``

Project Files
=============

Numerous files in the project space (root, app, public, writable) received updates. Due to
these files being outside of the system scope they will not be changed without your intervention.
There are some third-party CodeIgniter modules available to assist with merging changes to
the project space: `Explore on Packagist <https://packagist.org/explore/?query=codeigniter4%20updates>`_.

.. note:: Except in very rare cases for bug fixes, no changes made to files for the project space
    will break your application. All changes noted here are optional until the next major version,
    and any mandatory changes will be covered in the sections above.

Content Changes
---------------

The following files received significant changes (including deprecations or visual adjustments)
and it is recommended that you merge the updated versions with your application:

* ``app/Config/App.php``
* ``app/Config/Autoload.php``
* ``app/Config/Cookie.php``
* ``app/Config/Events.php``
* ``app/Config/Exceptions.php``
* ``app/Config/Security.php``
* ``app/Views/errors/html/*``
* ``env``
* ``spark``

All Changes
-----------

This is a list of all files in the project space that received changes;
many will be simple comments or formatting that have no affect on the runtime:

* ``app/Config/App.php``
* ``app/Config/Autoload.php``
* ``app/Config/ContentSecurityPolicy.php``
* ``app/Config/Cookie.php``
* ``app/Config/Events.php``
* ``app/Config/Exceptions.php``
* ``app/Config/Logger.php``
* ``app/Config/Mimes.php``
* ``app/Config/Modules.php``
* ``app/Config/Security.php``
* ``app/Controllers/BaseController.php``
* ``app/Views/errors/html/debug.css``
* ``app/Views/errors/html/error_404.php``
* ``app/Views/errors/html/error_exception.php``
* ``app/Views/welcome_message.php``
* ``composer.json``
* ``contributing/guidelines.rst``
* ``env``
* ``phpstan.neon.dist``
* ``phpunit.xml.dist``
* ``public/.htaccess``
* ``public/index.php``
* ``rector.php``
* ``spark``
