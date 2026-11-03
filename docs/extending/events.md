# Events

CodeIgniter's Events feature provides a means to tap into and modify the inner workings of the framework without hacking core files. When CodeIgniter runs, it follows a specific execution process. There may be instances, however, when you'd like to cause some action to take place at a particular stage in the execution process. For example, you might want to run a script right before your controllers get loaded, or right after, or you might want to trigger one of your own scripts in some other location.

Events work on a *publish/subscribe* pattern, where an event is triggered at some point during the script execution. Other scripts can "subscribe" to that event by registering with the Events class to let it know they want to perform an action when that event is triggered.

## Enabling Events

Events are always enabled, and are available globally.

## Defining an Event

Most events are defined within the **app/Config/Events.php** file. You can subscribe an action to an event with the `Events` class's `on()` method. The first parameter is the name of the event to subscribe to. The second parameter is a callable that will be run when that event is triggered:

```php
<?php

use CodeIgniter\Events\Events;

Events::on('pre_system', ['MyClass', 'myFunction']);
```

In this example, whenever the `pre_system` event is executed, an instance of `MyClass` is created and the `myFunction()` method is run. Note that the second parameter can be *any* form of [callable](https://www.php.net/manual/en/function.is-callable.php) that PHP recognizes:

```php
<?php

use CodeIgniter\Events\Events;

// Call a standalone function
Events::on('pre_system', 'some_function');

// Call on an instance method
$user = new \App\Libraries\User();
Events::on('pre_system', [$user, 'someMethod']);

// Call on a static method
Events::on('pre_system', 'SomeClass::someMethod');

// Use a Closure
Events::on('pre_system', static function (...$params) {
    // ...
});
```

### Setting Priorities

Since multiple methods can be subscribed to a single event, you will need a way to define in what order those methods are called. You can do this by passing a priority value as the third parameter of the `on()` method. Listeners with lower numeric priority values run first. Zero and negative integer values are also accepted:

```php
<?php

use CodeIgniter\Events\Events;

Events::on('post_controller_constructor', 'some_function', 25);
```

Any subscribers with the same priority will be executed in the order in which they were defined.

Since v4.2.0, three class constants are defined for your use that set some helpful ranges on the values. You are not required to use these but you might find they aid readability:

```php
<?php

use CodeIgniter\Events\Events;

Events::PRIORITY_LOW;    // 200
Events::PRIORITY_NORMAL; // 100
Events::PRIORITY_HIGH;   // 10
```

!!! important "Important"
    The constants `EVENT_PRIORITY_LOW`, `EVENT_PRIORITY_NORMAL` and `EVENT_PRIORITY_HIGH` have been removed in v4.6.0.

Once sorted, all subscribers are executed in order. If any subscriber returns a boolean false value, then execution of the subscribers will stop.

## Publishing your own Events

The Events library also makes it simple for you to create events in your own code. To use this feature, you would simply need to call the `trigger()` method on the **Events** class with the name of the event:

```php
<?php

\CodeIgniter\Events\Events::trigger('some_event');
```

You can pass any number of arguments to the subscribers by adding them as additional parameters. Subscribers will be given the arguments in the same order as defined:

```php
<?php

use CodeIgniter\Events\Events;

Events::trigger('some_event', $foo, $bar, $baz);

Events::on('some_event', static function ($foo, $bar, $baz) {
    // ...
});
```

## Simulating Events

During testing, you might not want the events to actually fire, as sending out hundreds of emails a day is both slow and counter-productive. You can tell the Events class to only simulate running the events with the `simulate()` method. When **true**, all events will be skipped over during the trigger method. Everything else will work as normal, though.

```php
<?php

use CodeIgniter\Events\Events;

Events::simulate(true);
```

You can stop simulation by passing false:

```php
<?php

use CodeIgniter\Events\Events;

Events::simulate(false);
```

## Event Points

### For Web Apps

The following is a list of available event points for web applications that are invoked by **public/index.php**:

- **pre_system** Called early during system execution. The URI, Request, and Response have been instantiated, but page cache checking, routing, and execution of "before" controller filters have not yet occurred.
- **post_controller_constructor** Called immediately after your controller is instantiated, but prior to any method calls happening.
- **post_system** Called right before the final rendered page is sent to the browser, at the end of system execution, after the execution of "after" controller filters.

### For CLI Apps

The following is a list of available event points for [Spark Commands](../cli/spark_commands.md):

- **pre_command** Called right before the command code execution.
- **post_command** Called right after the command code execution.

### Others

The following is a list of event points available for each of the libraries:

- **email** Called after an email is sent successfully from `CodeIgniter\Email\Email`. Receives an array of the `Email` class's properties as a parameter.
- **DBQuery** Called after a database query whether successful or not. Receives the `Query` object.
- **migrate** Called after a successful migration call to `latest()` or `regress()`. Receives the current properties of `MigrationRunner` as well as the name of the method.
