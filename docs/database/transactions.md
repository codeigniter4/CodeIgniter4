# Transactions

CodeIgniter's database abstraction allows you to use transactions with databases that support transaction-safe table types. In MySQL, you'll need to be running InnoDB or BDB table types rather than the more common MyISAM. Most other database platforms support transactions natively.

If you are not familiar with transactions, we recommend you find a good online resource to learn about them for your particular database. The information below assumes you have a basic understanding of transactions.

## CodeIgniter's Approach to Transactions

CodeIgniter utilizes an approach to transactions that is very similar to the process used by the popular database class ADODB. We've chosen that approach because it greatly simplifies the process of running transactions. In most cases, all that is required is two lines of code.

Traditionally, transactions have required a fair amount of work to implement since they demand that you keep track of your queries and determine whether to commit or roll back based on the success or failure of your queries. This is particularly cumbersome with nested queries. In contrast, we've implemented a smart transaction system that does all this for you automatically (you can also manage your transactions manually if you choose to, but there's really no benefit).

!!! note "Note"
    Since v4.3.0, during transactions, exceptions are not thrown by default even if `DBDebug` is true.

## Running Transactions

To run your queries using transactions, you will use the `$this->db->transStart()` and `$this->db->transComplete()` methods as follows:

```php
<?php

$this->db->transStart();
$this->db->query('AN SQL QUERY...');
$this->db->query('ANOTHER QUERY...');
$this->db->query('AND YET ANOTHER QUERY...');
$this->db->transComplete();
```

You can run as many queries as you want between the `transStart()`/`transComplete()` methods, and they will all be committed or rolled back based on the success or failure of any given query.

## Strict Mode

By default, CodeIgniter runs all transactions in Strict Mode.

When strict mode is enabled and you are running multiple groups of transactions, if one group fails, all subsequent groups will be rolled back.

If strict mode is disabled, each group is treated independently, meaning a failure of one group will not affect any others.

Strict Mode can be disabled as follows:

```php
<?php

$this->db->transStrict(false);
```

### Resetting Transaction Status

!!! success "Available from version 4.6.0"

When strict mode is enabled, if one transaction fails, all subsequent transactions will be rolled back.

If you want to restart transactions after a failure, you can reset the transaction status:

```php
<?php

$this->db->resetTransStatus();
```

## Managing Errors

!!! note "Note"
    Since v4.3.0, during transactions, exceptions are not thrown by default even if `DBDebug` is true.

You can manage your own errors like this:

```php
<?php

$this->db->transStart();
$this->db->query('AN SQL QUERY...');
$this->db->query('ANOTHER QUERY...');
$this->db->transComplete();

if ($this->db->transStatus() === false) {
    // generate an error... or use the log_message() function to log your error
}
```

## Throwing Exceptions

!!! success "Available from version 4.3.0"

!!! note "Note"
    Since v4.3.0, during transactions, exceptions are not thrown by default even if `DBDebug` is true.

If you want an exception to be thrown when a query error occurs, you can use `$this->db->transException(true)`:

```php
<?php

// When DBDebug in the Database Config must be true.

use CodeIgniter\Database\Exceptions\DatabaseException;

try {
    $this->db->transException(true)->transStart();
    $this->db->query('AN SQL QUERY...');
    $this->db->query('ANOTHER QUERY...');
    $this->db->query('AND YET ANOTHER QUERY...');
    $this->db->transComplete();
} catch (DatabaseException $e) {
    // Automatically rolled back already.
}
```

If a query error occurs, all the queries will be rolled back, and a `DatabaseException` will be thrown.

## Disabling Transactions

Transactions are enabled by default. If you would like to disable transactions, you can do so using `$this->db->transOff()`:

```php
<?php

$this->db->transOff();
$this->db->transStart();
$this->db->query('AN SQL QUERY...');
$this->db->transComplete();
```

When transactions are disabled, your queries will be auto-committed, just as they are when running queries without transactions.

## Test Mode

You can optionally put the transaction system into "test mode", which will cause your queries to be rolled back, even if the queries produce a valid result. To use test mode, simply set the first parameter in the `$this->db->transStart()` method to true:

```php
<?php

$this->db->transStart(true); // Query will be rolled back
$this->db->query('AN SQL QUERY...');
$this->db->transComplete();
```

## Running Transactions Manually

When `DBDebug` is false in your **app/Config/Database.php** file, and you would like to run transactions manually, you can do so as follows:

```php
<?php

$this->db->transBegin();

$this->db->query('AN SQL QUERY...');
$this->db->query('ANOTHER QUERY...');
$this->db->query('AND YET ANOTHER QUERY...');

if ($this->db->transStatus() === false) {
    $this->db->transRollback();
} else {
    $this->db->transCommit();
}
```

!!! note "Note"
    Make sure to use `$this->db->transBegin()` when running manual transactions, **NOT** `$this->db->transStart()`.

## MySQL Deadlocks

InnoDB error 1213 rolls the victim transaction back. CodeIgniter keeps that transaction open. The next statement run through `query()`, `simpleQuery()` or the Query Builder on the same connection starts a new server transaction, so the write does not autocommit. The rollback happens when the outermost transaction finishes: `transCommit()` rolls that server transaction back and returns false, so the transaction has ended when it returns, and `transComplete()` also rolls it back. At a nested depth, `transCommit()` still returns true and only decrements the depth.

Prepared queries are not covered. A deadlock raised by a prepared query is not detected, and a prepared query run after a deadlock does not start a new server transaction.

Error 1205 is not handled this way. `innodb_rollback_on_timeout` decides whether InnoDB rolled that transaction back, and the message is the same either way.

## Nested Transactions

In CodeIgniter, transactions can be nested in such a way that only the outermost or top-level transaction commands are executed. You can include as many pairs of `transStart()`/`transComplete()` or `transBegin()`/`transCommit()`/`transRollback()` as you want inside a transaction block and so on. CodeIgniter will keep track of the transaction "depth" and only take action at the outermost layer (zero depth).

```php
<?php

$this->db->transStart(); // actually starts a transaction
$this->db->query('SOME QUERY 1 ...');
$this->db->transStart(); // doesn't necessarily start another transaction
$this->db->query('SOME QUERY 2 ...');
$this->db->transComplete(); // doesn't necessarily end the transaction, but required to finish the inner transaction
$this->db->query('SOME QUERY 3 ...');
$this->db->transComplete(); // actually ends the transaction
```

!!! note "Note"
    In case the structure is far more complex, it's your responsibility to ensure that the inner transactions can reach the outermost layer again in order to be fully executed by the database, thus preventing unintended commits/rollbacks.
