# Benchmarking

CodeIgniter provides two separate tools to help you benchmark your code and test different options: the **Timer** and the **Iterator**. The Timer allows you to easily calculate the time between two points in the execution of your script. The Iterator allows you to set up several variations and run those tests, recording performance and memory statistics to help you decide which version is the best.

The Timer class is always active, being started from the moment the framework is invoked until right before sending the output to the user, enabling a very accurate timing of the entire system execution.

## Using the Timer

With the Timer, you can measure the time between two moments in the execution of your application. This makes it simple to measure the performance of different aspects of your application. All measurement is done using the `start()` and `stop()` methods.

### Timer::start()

The `start()` method takes a single parameter: the name of this timer. You can use any string as the name of the timer. It is only used as a later reference, to know which measurement is which:

```php
<?php

$benchmark = service('timer');
$benchmark->start('render view');
```

### Timer::stop()

The `stop()` method also takes the name of the timer that you want to stop as its only parameter:

```php
<?php

$benchmark->stop('render view');
```

The name is not case-sensitive, but otherwise must match the name you gave it when you started the timer.

### timer()

Alternatively, you can use the [global function](../general/common_functions.md) `timer()` to start and stop timers:

```php
<?php

// Start the timer
timer('render view');
// Stop a running timer,
// if one of this name has been started
timer('render view');
```

### Timer::record()

!!! success "Available from version 4.3.0"

Since v4.3.0, if you use very small code blocks to benchmark, you can also use the `record()` method. It accepts a no-parameter callable and measures its execution time. The `start()` and `stop()` methods will be called automatically around the function call.

```php
<?php

$benchmark->record('slow_function', static function () { slow_function('...'); });

/*
 * Same as:
 *
 * $benchmark->start('slow_function');
 * slow_function('...');
 * $benchmark->stop('slow_function');
*/
```

You can also return the callable's return value for further processing.

```php
<?php

$length = $benchmark->record('string length', static fn () => strlen('CI4'));

/*
 * $length = 3
 *
 * Same as:
 *
 * $benchmark->start('string length');
 * $length = strlen('CI4');
 * $benchmark->stop('string length');
*/
```

The same functionality is also available when passing a callable to `timer()` as the second parameter.

```php
<?php

$length = timer('string length', static fn () => strlen('CI4'));

/*
 * $length = 3
 *
 * Same as:
 *
 * timer('string length');
 * $length = strlen('CI4');
 * timer('string length');
*/
```

### Viewing Your Benchmark Points

When your application runs, all of the timers that you have set are collected by the Timer class. It does not automatically display them, though. You can retrieve all of your timers by calling the `getTimers()` method. This returns an array of benchmark information, including start, end, and duration:

```php
<?php

$timers = $benchmark->getTimers();
/*
 * Produces:
 * [
 *     'render view'  => [
 *         'start'    => 1234567890,
 *         'end'      => 1345678920,
 *         'duration' => 15.4315, // number of seconds
 *     ]
 * ]
 */
```

You can change the precision of the calculated duration by passing in the number of decimal places you want to be shown as the only parameter. The default value is 4 digits after the decimal point:

```php
<?php

$timers = $benchmark->getTimers(6);
```

The timers are automatically displayed in the [Debug Toolbar](debugging.md).

### Displaying Execution Time

While the `getTimers()` method will give you the raw data for all of the timers in your project, you can retrieve the duration of a single timer, in seconds, with the `getElapsedTime()` method. The first parameter is the name of the timer to display. The second is the number of decimal places to display. This defaults to 4:

```php
<?php

echo timer()->getElapsedTime('render view');
// Displays: 0.0234
```

## Using the Iterator

The Iterator is a simple tool that is designed to allow you to try out multiple variations on a solution to see the speed differences and different memory usage patterns. You can add any number of "tasks" for it to run and the class will run the task hundreds or thousands of times to get a clearer picture of performance. The results can then be retrieved and used by your script, or displayed as an HTML table.

### Creating Tasks To Run

Tasks are defined within Closures. Any output the task creates will be discarded automatically. They are added to the Iterator class through the `add()` method. The first parameter is a name you want to refer to this test by. The second parameter is the Closure itself:

```php
<?php

$iterator = new \CodeIgniter\Debug\Iterator();

$iterator->add('double', static function ($word = 'little') {
    "Some basic {$word} string test.";
});
```

### Running the Tasks

Once you've added the tasks to run, you can use the `run()` method to loop over the tasks many times. By default, it will run each task 1000 times. This is probably sufficient for most simple tests. If you need to run the tests more times than that, you can pass the number as the first parameter:

```php
<?php

// Run the tests 3000 times.
$htmlTable = $iterator->run(3000);
```

Once it has run, it will return an HTML table with the results of the test. If you don't want the results, you can pass in `false` as the second parameter:

```php
<?php

// Returns null.
$iterator->run(1000, false);
```
