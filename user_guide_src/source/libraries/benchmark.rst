############
Benchmarking
############

CodeIgniter provides two separate tools to help you benchmark your code and test different options:
the Timer and the Iterator. The Timer allows you to easily calculate the time between two points in the
execution of your script. The Iterator allows you to setup several variations and run those tests, recording
performance and memory statistics to help you decide which version is the best.

The Timer class is always active, being started from the moment the framework is invoked until right before
sending the output to the user, enabling a very accurate timing of the entire system execution.

* [Using the Timer](#timer)
	* [Profiling Your Benchmark Points](#timer_profiling)
	* [Displaying Execution Time](#timer_execution)
* [Using the Iterator](#iterator)
	* [Creating Tasks To Run](#tasks)
	* [Running the Tasks](#running)
* [Timer Class Reference](#timer_ref)
* [Iterator Class Reference](#iterator_ref)

<a name="timer"></a>
Using the Timer
===============

Using the Timer, you can measure the time between two moments in the execution of your application. This makes
it simple to measure the performance of different aspects of your application. All measurement is done using
the ``start()`` and ``stop()`` methods.

The ``start()`` methods takes a single parameter: the name of this timer. You can use any string as the name
of the timer. It is only used for you to reference later to know which measurement is which::

	$benchmark = DI()->single('bmtimer');
	$benchmark->start('render view');

The ``stop()`` method takes the name of the timer that you want to stop as the only parameter, also::

	$benchmark->stop('render view');

The name is not case-sensitive, but otherwise must match the name you gave it when you started the timer.

<a name="timer_profiling"></a>
Viewing Your Benchmark Points
=============================

When your application runs, all of the timers that you have set are collected by the Timer class. It does
not automatically display them, though. You can retrieve all of your timers by calling the ``timers()`` method.
This returns an array of benchmark information, including start, end, and duration::

	$timers = $benchmark->timers();
	
	// Timers =
	array(
		'render view' => array(
			'start' => 1234567890,
			'end' => 1345678920,
			'duration' => 15.4315      // number of seconds
		)
	)

You can change the precision of the calculated duration by passing in the number of decimal places you want shown as
the only parameter. The default value is 4 numbers behind the decimal point::

	$timers = $benchmark->timers(6);

<a name="timer_execution"></a>
Displaying Execution Time
=========================

While the ``timers()`` method will give you the raw data for all of the timers in your project, you can retrieve
the duration of a single timer, in seconds, with the `elapsedTime()` method. The first parameter is the name of
the timer to display. The second is the number of decimal places to display. This defaults to 4::

	echo $benchmark->elapsedTime('render view');
	// Displays: 0.0234

<a name="iterator"></a>
Using the Iterator
==================

The Iterator is a simple tool that is designed to allow you to try out multiple variations on a solution to
see the speed differences and different memory usage patterns. You can add any number of "tasks" for it to
run and the class will run the task hundreds or thousands of times to get a clearer picture of performance.
The results can then be retrieved and used by your script, or displayed as an HTML table.

<a name="tasks"></a>
Creating Tasks To Run
=====================

Tasks are defined within Closures. Any output the task creates will be discarded automatically. They are
added to the Iterator class through the `add()` method. The first parameter is a name you want to refer to
this test by. The second parameter is the Closure, itself::

	$iterator = new CodeIgniter\Benchmark\Iterator();
	
	// Add a new task
	$iterator->add('single_concat', function() 
		{
			$str = 'Some basic'.'little'.'string concatenation test.';
		}
	);
	
	// Add another task
	$iterator->add('double', function($a='little')
		{
			$str = "Some basic {$little} string test.";
		}
	);

<a name="running"></a>
Running the Tasks
=================

Once you've added the tasks to run, you can use the ``run()`` method to loop over the tasks many times.
By default, it will run each task 1000 times. This is probably sufficient for most simple tests. If you need
to run the tests more times than that, you can pass the number as the first parameter::

	// Run the tests 3000 times.
	$iterator->run(3000);

Once it has ran, it will return an HTML table with the results of the test. If you don't want the results
displayed, you can pass in `false` as the second parameter::

	// Don't display the results.
	$iterator->run(1000, false);


<a name="timer_ref"></a>
## Timer Class Reference

<a name="iterator_ref"></a>
## Iterator Class Reference