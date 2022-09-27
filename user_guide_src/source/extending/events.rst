Events
#####################################

CodeIgniter's Events feature provides a means to tap into and modify the inner workings of the framework without hacking
core files. When CodeIgniter runs it follows a specific execution process. There may be instances, however, when you'd
like to cause some action to take place at a particular stage in the execution process. For example, you might want to run
a script right before your controllers get loaded, or right after, or you might want to trigger one of your own scripts
in some other location.

Events work on a *publish/subscribe* pattern, where an event, is triggered at some point during the script execution.
Other scripts can "subscribe" to that event by registering with the Events class to let it know they want to perform an
action when that event is triggered.

.. contents::
    :local:
    :depth: 2

Enabling Events
===============

Events are always enabled, and are available globally.

Defining an Event
=================

Most events are defined within the **app/Config/Events.php** file. You can subscribe an action to an event with
the ``Events`` class' ``on()`` method. The first parameter is the name of the event to subscribe to. The second parameter is
a callable that will be run when that event is triggered:

.. literalinclude:: events/001.php

In this example, whenever the ``pre_system`` event is executed, an instance of ``MyClass`` is created and the
``myFunction()`` method is run. Note that the second parameter can be *any* form of
`callable <https://www.php.net/manual/en/function.is-callable.php>`_ that PHP recognizes:

.. literalinclude:: events/002.php

Setting Priorities
------------------

Since multiple methods can be subscribed to a single event, you will need a way to define in what order those methods
are called. You can do this by passing a priority value as the third parameter of the ``on()`` method. Lower values
are executed first, with a value of 1 having the highest priority, and there being no limit on the lower values:

.. literalinclude:: events/003.php

Any subscribers with the same priority will be executed in the order they were defined.

Since v4.2.0, three class constants are defined for your use, that set some helpful ranges on the values. You are not required to use these
but you might find they aid readability:

.. literalinclude:: events/004.php

.. important:: The constants ``EVENT_PRIORITY_LOW``, ``EVENT_PRIORITY_NORMAL`` and ``EVENT_PRIORITY_HIGH`` are deprecated, and the definitions are moved to ``app/Config/Constants.php``. These will be removed in future releases.

Once sorted, all subscribers are executed in order. If any subscriber returns a boolean false value, then execution of
the subscribers will stop.

Publishing your own Events
==========================

The Events library makes it simple for you to create events in your own code, also. To use this feature, you would simply
need to call the ``trigger()`` method on the **Events** class with the name of the event:

.. literalinclude:: events/005.php

You can pass any number of arguments to the subscribers by adding them as additional parameters. Subscribers will be
given the arguments in the same order as defined:

.. literalinclude:: events/006.php

Simulating Events
=================

During testing, you might not want the events to actually fire, as sending out hundreds of emails a day is both slow
and counter-productive. You can tell the Events class to only simulate running the events with the ``simulate()`` method.
When **true**, all events will be skipped over during the trigger method. Everything else will work as normal, though.

.. literalinclude:: events/007.php

You can stop simulation by passing false:

.. literalinclude:: events/008.php

Event Points
============

The following is a list of available event points within the CodeIgniter core code:

* **pre_system** Called very early during system execution. Only the benchmark and events class have been loaded at this point. No routing or other processes have happened.
* **post_controller_constructor** Called immediately after your controller is instantiated, but prior to any method calls happening.
* **post_system** Called after the final rendered page is sent to the browser, at the end of system execution after the finalized data is sent to the browser.
* **email** Called after an email sent successfully from ``CodeIgniter\Email\Email``. Receives an array of the ``Email`` class's properties as a parameter.
* **DBQuery** Called after a database query whether successful or not. Receives the ``Query`` object.
* **migrate** Called after a successful migration call to ``latest()`` or ``regress()``. Receives the current properties of ``MigrationRunner`` as well as the name of the method.
