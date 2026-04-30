############
Transactions
############

CodeIgniter's database abstraction allows you to use transactions with
databases that support transaction-safe table types. In MySQL, you'll
need to be running InnoDB or BDB table types rather than the more common
MyISAM. Most other database platforms support transactions natively.

If you are not familiar with transactions we recommend you find a good
online resource to learn about them for your particular database. The
information below assumes you have a basic understanding of
transactions.

.. contents::
    :local:
    :depth: 3

CodeIgniter's Approach to Transactions
======================================

CodeIgniter utilizes an approach to transactions that is very similar to
the process used by the popular database class ADODB. We've chosen that
approach because it greatly simplifies the process of running
transactions. In most cases, all that is required is two lines of code.

Traditionally, transactions have required a fair amount of work to
implement since they demand that you keep track of your queries and
determine whether to commit or rollback based on the success or failure
of your queries. This is particularly cumbersome with nested queries. In
contrast, we've implemented a smart transaction system that does all
this for you automatically (you can also manage your transactions
manually if you choose to, but there's really no benefit).

.. note::
    Since v4.3.0, during transactions, exceptions are not thrown by default
    even if ``DBDebug`` is true.

Running Transactions
====================

To run your queries using transactions you will use the
``$this->db->transStart()`` and ``$this->db->transComplete()`` methods as
follows:

.. literalinclude:: transactions/001.php

You can run as many queries as you want between the ``transStart()``/``transComplete()``
methods and they will all be committed or rolled back based on the success
or failure of any given query.

.. _transactions-closure:

Running Transactions with a Closure
===================================

.. versionadded:: 4.8.0

You may also run a transaction with the ``transaction()`` method. It starts a
transaction, runs the callback, commits when the callback completes
successfully, and rolls back if the callback throws an exception:

.. literalinclude:: transactions/012.php

The callback receives the current database connection as its only argument.
If the transaction commits successfully, ``transaction()`` returns the callback
return value. If the transaction cannot begin, or if a query failure marks the
transaction as failed without throwing an exception, ``transaction()`` rolls
back and returns ``false``.

If transactions are disabled, ``transaction()`` does not start a transaction.
It runs the callback and returns the callback result.

If the callback throws an exception, ``transaction()`` rolls back and rethrows
the original exception.

If an ``afterRollback()`` callback throws while ``transaction()`` is rolling
back, that callback exception bubbles to the caller instead of the normal
``false`` return value or the original callback exception.

Callbacks registered with ``afterCommit()`` or ``afterRollback()`` inside the
transaction callback follow the same rules as other transaction callbacks: they
run only after the outermost transaction commits or rolls back.

Strict Mode
===========

By default, CodeIgniter runs all transactions in Strict Mode.

When strict mode is enabled, if you are running multiple groups of transactions,
if one group fails all subsequent groups will be rolled back.

If strict mode is disabled, each group is treated independently, meaning a failure
of one group will not affect any others.

Strict Mode can be disabled as follows:

.. literalinclude:: transactions/002.php

.. _transactions-resetting-transaction-status:

Resetting Transaction Status
----------------------------

.. versionadded:: 4.6.0

When strict mode is enabled, if one transaction fails, all subsequent transactions
will be rolled back.

If you wan to restart transactions after a failure, you can reset the transaction
status:

.. literalinclude:: transactions/009.php

.. _transactions-managing-errors:

Managing Errors
===============

.. note::
    Since v4.3.0, during transactions, exceptions are not thrown by default
    even if ``DBDebug`` is true.

You can manage your own errors like this:

.. literalinclude:: transactions/003.php

.. _transactions-throwing-exceptions:

Throwing Exceptions
===================

.. versionadded:: 4.3.0

.. note::
    Since v4.3.0, during transactions, exceptions are not thrown by default
    even if ``DBDebug`` is true.

If you want an exception to be thrown when a query error occurs, you can use
``$this->db->transException(true)``:

.. literalinclude:: transactions/008.php

If a query error occurs, all the queries will be rolled backed, and a
``DatabaseException`` will be thrown.

.. _transactions-transaction-callbacks:

Running Code after Commit or Rollback
=====================================

.. versionadded:: 4.8.0

You may register callbacks to run only after the outermost transaction has
successfully committed by using the ``afterCommit()`` method, or after the
outermost transaction has rolled back by using the ``afterRollback()`` method:

.. literalinclude:: transactions/010.php

Callbacks registered during an active transaction are delayed until the
outermost transaction commits or rolls back.

If the transaction commits, ``afterCommit()`` callbacks run and
``afterRollback()`` callbacks are discarded. If the transaction rolls back,
``afterRollback()`` callbacks run and ``afterCommit()`` callbacks are discarded.
If no transaction is active, ``afterCommit()`` callbacks run immediately, while
``afterRollback()`` callbacks are not run.

For example:

.. literalinclude:: transactions/011.php

.. note:: When ``afterCommit()`` is called outside an active transaction, it runs
    immediately. This includes calls from Model ``beforeInsert`` or
    ``beforeUpdate`` events when the calling code has not already started a
    transaction, so the callback may run before the Model's insert or update
    query is executed.

This is useful for side effects that should only happen after committed data is
visible, such as dispatching a queued job or sending a notification, and for
cleanup that should only happen after a real rollback.

Callbacks run after the database transaction has already committed or rolled
back. If a callback throws an exception, that exception bubbles to the caller,
but the transaction outcome is not changed.

.. warning:: When multiple callbacks are registered for the same transaction
    outcome, they run in registration order. If one callback throws an exception,
    the subsequent callbacks are not run.

Rollback callbacks also run when CodeIgniter automatically rolls back an active
transaction while handling a transaction failure or cleaning up an unfinished
transaction.

Disabling Transactions
======================

Transactions are enabled by default. If you would like to disable transactions you
can do so using ``$this->db->transOff()``:

.. literalinclude:: transactions/004.php

When transactions are disabled, your queries will be auto-committed, just
as they are when running queries without transactions.

Test Mode
=========

You can optionally put the transaction system into "test mode", which
will cause your queries to be rolled back -- even if the queries produce
a valid result. To use test mode simply set the first parameter in the
``$this->db->transStart()`` method to true:

.. literalinclude:: transactions/005.php

.. _transactions-manual-transactions:

Running Transactions Manually
=============================

When you have ``DBDebug`` false in your **app/Config/Database.php** file, and
if you would like to run transactions manually you can do so as follows:

.. literalinclude:: transactions/006.php

.. note:: Make sure to use ``$this->db->transBegin()`` when running manual
    transactions, **NOT** ``$this->db->transStart()``.

Nested Transactions
===================

In CodeIgniter, transactions can be nested in a way such that only the
outmost or top-level transaction commands are executed. You can include as
many pairs of ``transStart()``/``transComplete()`` or ``transBegin()``/``transCommit()``/``transRollback()``
as you want inside a transaction block and so on. CodeIgniter will keep
track of the transaction "depth" and only take action at the outermost layer
(zero depth).

.. literalinclude:: transactions/007.php

.. note:: In case the structure is far more complex, it's your responsibility
    to ensure that the inner transactions can reach the outermost layer again
    in order to be fully executed by the database, thus prevents unintended
    commits/rollbacks.
