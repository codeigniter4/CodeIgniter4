**************************
Debugging Your Application
**************************

.. contents::
    :local:
    :depth: 2

================
Replace var_dump
================

While using XDebug and a good IDE can be indispensable to debug your application, sometimes a quick ``var_dump()`` is
all you need. CodeIgniter makes that even better by bundling in the excellent `Kint <https://kint-php.github.io/kint/>`_
debugging tool for PHP. This goes way beyond your usual tool, providing many alternate pieces of data, like formatting
timestamps into recognizable dates, showing you hexcodes as colors, display array data like a table for easy reading,
and much, much more.

Enabling Kint
=============

By default, Kint is enabled in **development** and **testing** :doc:`environments </general/environments>` only.
It will be enabled whenever the constant ``CI_DEBUG`` is defined and its value is truthy.
This is defined in the boot files (e.g. **app/Config/Boot/development.php**).

Using Kint
==========

d()
---

The ``d()`` method dumps all of the data it knows about the contents passed as the only parameter to the screen, and
allows the script to continue executing:

.. literalinclude:: debugging/001.php

dd()
----

This method is identical to ``d()``, except that it also ``die()`` and no further code is executed this request.

trace()
-------

This provides a backtrace to the current execution point, with Kint's own unique spin:

.. literalinclude:: debugging/002.php

For more information, see `Kint's page <https://kint-php.github.io/kint//>`_.

.. _the-debug-toolbar:

=================
The Debug Toolbar
=================

The Debug Toolbar provides at-a-glance information about the current page request, including benchmark results,
queries you have run, request and response data, and more. This can all prove very useful during development
to help you debug and optimize.

.. note:: The Debug Toolbar is still under construction with several planned features not yet implemented.

Enabling the Toolbar
====================

The toolbar is enabled by default in any :doc:`environment </general/environments>` *except* **production**. It will be shown whenever the
constant ``CI_DEBUG`` is defined and its value is truthy. This is defined in the boot files (e.g.
**app/Config/Boot/development.php**) and can be modified there to determine what environment to show.

.. note:: The Debug Toolbar is not displayed when your ``baseURL`` setting (in **app/Config/App.php** or ``app.baseURL`` in **.env**) does not match your actual URL.

The toolbar itself is displayed as an :doc:`After Filter </incoming/filters>`. You can stop it from ever
running by removing it from the ``$globals`` property of **app/Config/Filters.php**.

Choosing What to Show
---------------------

CodeIgniter ships with several Collectors that, as the name implies, collect data to display on the toolbar. You
can easily make your own to customize the toolbar. To determine which collectors are shown, again head over to
the **app/Config/Toolbar.php** configuration file:

.. literalinclude:: debugging/003.php

Comment out any collectors that you do not want to show. Add custom Collectors here by providing the fully-qualified
class name. The exact collectors that appear here will affect which tabs are shown, as well as what information is
shown on the Timeline.

.. note:: Some tabs, like Database and Logs, will only display when they have content to show. Otherwise, they
    are removed to help out on smaller displays.

The Collectors that ship with CodeIgniter are:

* **Timers** collects all of the benchmark data, both by the system and by your application.
* **Database** Displays a list of queries that all database connections have performed, and their execution time.
* **Logs** Any information that was logged will be displayed here. In long-running systems, or systems with many items being logged, this can cause memory issues and should be disabled.
* **Views** Displays render time for views on the timeline, and shows any data passed to the views on a separate tab.
* **Cache** Will display information about cache hits and misses, and execution times.
* **Files** displays a list of all files that have been loaded during this request.
* **Routes** displays information about the current route and all routes defined in the system.
* **Events** displays a list of all events that have been loaded during this request.

Setting Benchmark Points
========================

In order for the Profiler to compile and display your benchmark data you must name your mark points using specific syntax.

Please read the information on setting Benchmark points in the :doc:`Benchmark Library </testing/benchmark>` page.

Creating Custom Collectors
==========================

Creating custom collectors is a straightforward task. You create a new class, fully-namespaced so that the autoloader
can locate it, that extends ``CodeIgniter\Debug\Toolbar\Collectors\BaseCollector``. This provides a number of methods
that you can override, and has four required class properties that you must correctly set depending on how you want
the Collector to work

.. literalinclude:: debugging/004.php

**$hasTimeline** should be set to ``true`` for any Collector that wants to display information in the toolbar's
timeline. If this is true, you will need to implement the ``formatTimelineData()`` method to format and return the
data for display.

**$hasTabContent** should be ``true`` if the Collector wants to display its own tab with custom content. If this
is true, you will need to provide a ``$title``, implement the ``display()`` method to render out tab's contents,
and might need to implement the ``getTitleDetails()`` method if you want to display additional information just
to the right of the tab content's title.

**$hasVarData** should be ``true`` if this Collector wants to add additional data to the ``Vars`` tab. If this
is true, you will need to implement the ``getVarData()`` method.

**$title** is displayed on open tabs.

Displaying a Toolbar Tab
------------------------

To display a toolbar tab you must:

1. Fill in ``$title`` with the text displayed as both the toolbar title and the tab header.
2. Set ``$hasTabContent`` to ``true``.
3. Implement the ``display()`` method.
4. Optionally, implement the ``getTitleDetails()`` method.

The ``display()`` creates the HTML that is displayed within the tab itself. It does not need to worry about
the title of the tab, as that is automatically handled by the toolbar. It should return a string of HTML.

The ``getTitleDetails()`` method should return a string that is displayed just to the right of the tab's title.
it can be used to provide additional overview information. For example, the Database tab displays the total
number of queries across all connections, while the Files tab displays the total number of files.

Providing Timeline Data
-----------------------

To provide information to be displayed in the Timeline you must:

1. Set ``$hasTimeline`` to ``true``.
2. Implement the ``formatTimelineData()`` method.

The ``formatTimelineData()`` method must return an array of arrays formatted in a way that the timeline can use
it to sort it correctly and display the correct information. The inner arrays must include the following information:

.. literalinclude:: debugging/005.php

Providing Vars
--------------

To add data to the Vars tab you must:

1. Set ``$hasVarData`` to ``true``
2. Implement ``getVarData()`` method.

The ``getVarData()`` method should return an array containing arrays of key/value pairs to display. The name of the
outer array's key is the name of the section on the Vars tab:

.. literalinclude:: debugging/006.php

.. _debug-toolbar-hot-reload:

Hot Reloading
=============

.. versionadded:: 4.4.0

The Debug Toolbar includes a feature called Hot Reloading that allows you to make changes to your application's code and have them automatically reloaded in the browser without having to refresh the page. This is a great time-saver during development.

To enable Hot Reloading while you are developing, you can click the button on the left side of the toolbar that looks like a refresh icon. This will enable Hot Reloading for all pages until you disable it.

Hot Reloading works by scanning the files within the **app** directory every second and looking for changes. If it finds any, it will send a message to the browser to reload the page. It does not scan any other directories, so if you are making changes to files outside of the **app** directory, you will need to manually refresh the page.

If you need to watch files outside of the **app** directory, or are finding it slow due to the size of your project, you can specify the directories to scan and the file extensions to scan for in the ``$watchedDirectories`` and ``$watchedExtensions`` properties of the **app/Config/Toolbar.php** configuration file.
