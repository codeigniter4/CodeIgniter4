####################################
Hooks - Extending the Framework Core
####################################

CodeIgniter's Hooks feature provides a means to tap into and modify the inner workings of the framework without hacking
core files. When CodeIgniter runs it follows a specific execution process. There may be instances, however, when you'd
like to cause some action to take place at a particular stage in the execution process. For example, you might want to run
a script right before your controllers get loaded, or right after, or you might want to trigger one of your own scripts
in some other location.

Hooks work on a *publish/subscribe* pattern, where a hook, or event, is triggered at some point during the script execution.
Other scripts can "subscribe" to that event by registering with the Hooks class to let it know they want to perform an
action when that hook is triggered.

Enabling Hooks
==============

Hooks are always enabled, and are available globally.

Defining a Hook
===============

Most hooks are defined within the **application/Config/Hooks.php** file. You can subscribe an action to a hook with
the Hooks class' ``on()`` method. The first parameter is the name of the hook to subscribe to. The second parameter is
a callable that will be run when that event is triggered::

	use CodeIgniter\Hooks\Hooks;

	Hooks::on('pre_system', ['MyClass', 'MyFunction']);

In this example, whenever the **pre_controller** hook is executed, an instance of ``MyClass`` is created and the
``MyFunction`` method is ran. Note that the second parameter can be *any* form of
`callable <http://php.net/manual/en/function.is-callable.php>`_ that PHP recognizes::

	// Call a standalone function
	Hooks::on('pre_system', 'some_function');

	// Call on an instance method
	$user = new User();
	Hooks::on('pre_system', [$user, 'some_method']);

	// Call on a static method
	Hooks::on('pre_system', 'SomeClass::someMethod');

	// Use a Closure
	Hooks::on('pre_system', function(...$params)
	{
		. . .
	});

Setting Priorities
------------------

Since multiple methods can be subscribed to a single event, you will need a way to define in what order those methods
are called. You can do this by passing a priority value as the third parameter of the ``on()`` method. Lower values
are executed first, with a value of 1 having the highest priority, and there being no limit on the lower values::

    Hooks::on('post_controller_constructor', 'some_function', 25);

Any subscribers with the same priority will be executed in the order they were defined.

Three constants are defined for your use, that set some helpful ranges on the values. You are not required to use these
but you might find they aid readability::

	define('HOOKS_PRIORITY_LOW', 200);
	define('HOOKS_PRIORITY_NORMAL', 100);
	define('HOOKS_PRIORITY_HIGH', 10);

Once sorted, all subscribers are executed in order. If any subscriber returns a boolean false value, then execution of
the subscribers will stop.

Publishing your own Hooks
=========================

The Hooks library makes it simple for you to create hooks into your own code, also. To use this feature, you would simply
need to call the ``trigger()`` method on the **Hooks** class with the name of the hook::

	\CodeIgniter\Hooks\Hooks::trigger('some_hook');

You can pass any number of arguments to the subscribers by adding them as additional parameters. Subscribers will be
given the arguments in the same order as defined::

	\CodeIgniter\Hooks\Hooks::trigger('some_hook', $foo, $bar, $baz);

	Hooks::on('some_hook', function($foo, $bar, $baz) {
		...
	});

Hook Points
===========

The following is a list of available hook points:

* **pre_system** Called very early during system execution. Only the benchmark and hooks class have been loaded at this point. No routing or other processes have happened.
* **post_controller_constructor** Called immediately after your controller is instantiated, but prior to any method calls happening.
* **post_system** Called after the final rendered page is sent to the browser, at the end of system execution after the finalized data is sent to the browser.

Hooks are closely related to :doc:`Filters </general/filters>`, and you should be sure to read up on them.
