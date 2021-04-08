######################################
Upgrading from 4.1.1 to 4.1.2
######################################

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
