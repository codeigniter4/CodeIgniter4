#############################
Upgrading from 4.1.4 to 4.2.0
#############################

**Changes for set() method in BaseBuilder and Model class**

The casting for the ``$value`` parameter has been removed to fix a bug where passing parameters as array and string
to the ``set()`` method were handled differently. If you extended the ``BaseBuilder`` class or ``Model`` class yourself
and modified the ``set()`` method, then you need to change its definition from
``public function set($key, ?string $value = '', ?bool $escape = null)`` to
``public function set($key, $value = '', ?bool $escape = null)``.

**Session DatabaseHandler's database table change**

The types of the following columns in the session table have been changed for optimization.

- MySQL
    - ``timestamp``
- PostgreSQL
    - ``ip_address``
    - ``timestamp``
    - ``data``

Update the definition of the session table. See the :doc:`/libraries/sessions` for the new definition.

The change was introduced in v4.1.2. But due to `a bug <https://github.com/codeigniter4/CodeIgniter4/issues/4807>`_,
the DatabaseHandler Driver did not work properly.
