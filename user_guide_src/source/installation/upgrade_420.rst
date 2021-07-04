#############################
Upgrading from 4.1.3 to 4.2.0
#############################

**Changes for set() method in BaseBuilder and Model class**

The casting for the ``$value`` parameter has been removed to fix a bug where passing parameters as array and string
to the ``set()`` method were handled differently. If you extended the ``BaseBuilder`` class or ``Model`` class yourself
and modified the ``set()`` method, then you need to change its definition from
``public function set($key, ?string $value = '', ?bool $escape = null)`` to
``public function set($key, $value = '', ?bool $escape = null)``.

