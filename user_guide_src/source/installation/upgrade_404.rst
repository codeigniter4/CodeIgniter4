#############################
Upgrading from 4.0.x to 4.0.4
#############################

CodeIgniter 4.0.4 fixes a bug in the implementation of :doc:`Controller Filters </incoming/filters>`, breaking
code implementing the ``FilterInterface``.

**Update FilterInterface declarations**

The method signatures for ``after()`` and ``before()`` must be updated to include ``$arguments``. The function
definitions should be changed from:

.. literalinclude:: upgrade_404/001.php
   :lines: 2-

to:

.. literalinclude:: upgrade_404/002.php
   :lines: 2-
