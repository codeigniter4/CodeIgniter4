######################################
Upgrading from 4.1.1 to 4.1.2
######################################

**BaseConnection::query() return values**

`BaseConnection::query()` method in prior versions was incorrectly returning BaseResult objects
even if the query failed. This method will now return ``false`` for failed queries (or throw an
Exception if ``DBDebug==true``) and will return booleans for write-type queries. Review any use
of ``query()`` method and be assess whether the value might be boolean instead of Result object.
For a better idea of what queries are write-type queries, check ``BaseConnection::isWriteType()``
and any DBMS-specific override ``isWriteType()`` in the relevant Connection class. 

**ConnectionInterface::isWriteType() declaration added**

If you have written any classes that implement ConnectionInterface, these must now implement the
``isWriteType()`` method, declared as ``public function isWriteType($sql): bool``. If your class extends BaseConnection, then that class will provide a basic ``isWriteType()``
method which you might want to override.
