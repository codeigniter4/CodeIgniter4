###########
CLI Library
###########

CodeIgniter's CLI library makes creating interactive command-line scripts simple, including:

* Prompting the user for more information
* Writing multi-colored text the terminal
* Beeping (be nice!)
* Showing progress bars during long tasks
* Wrapping long text lines to fit the window.

.. contents::
    :local:
    :depth: 2

Initializing the Class
**********************

You do not need to create an instance of the CLI library, since all of it's methods are static. Instead, you simply
need to ensure your controller can locate it via a ``use`` statement above your class:

.. literalinclude:: cli_library/001.php

The class is automatically initialized when the file is loaded the first time.

Getting Input from the User
***************************

Sometimes you need to ask the user for more information. They might not have provided optional command-line
arguments, or the script may have encountered an existing file and needs confirmation before overwriting. This is
handled with the ``prompt()`` or ``promptByKey()`` method.

.. note:: Since v4.3.0, you can write tests for these methods with ``PhpStreamWrapper``.
    See :ref:`testing-cli-input`.

prompt()
========

You can provide a question by passing it in as the first parameter:

.. literalinclude:: cli_library/002.php

You can provide a default answer that will be used if the user just hits enter by passing the default in the
second parameter:

.. literalinclude:: cli_library/003.php

You can restrict the acceptable answers by passing in an array of allowed answers as the second parameter:

.. literalinclude:: cli_library/004.php

Finally, you can pass :ref:`validation <validation>` rules to the answer input as the third parameter:

.. literalinclude:: cli_library/005.php

Validation rules can also be written in the array syntax:

.. literalinclude:: cli_library/006.php

promptByKey()
=============

Predefined answers (options) for prompt sometimes need to be described or are too complex to select via their value.
``promptByKey()`` allows the user to select an option by its key instead of its value:

.. literalinclude:: cli_library/007.php

Named keys are also possible:

.. literalinclude:: cli_library/008.php

Finally, you can pass :ref:`validation <validation>` rules to the answer input as the third parameter, the acceptable answers are automatically restricted to the passed options.

.. _prompt-by-multiple-keys:

promptByMultipleKeys()
======================

.. versionadded:: 4.3.0

This method is the same as ``promptByKey()``, but it supports multiple value.

.. literalinclude:: cli_library/023.php

.. important:: The method ``promptByMultipleKeys()``, unlike ``promptByKey()``, does not support named keys or validation.

Providing Feedback
******************

write()
=======

Several methods are provided for you to provide feedback to your users. This can be as simple as a single status update
or a complex table of information that wraps to the user's terminal window. At the core of this is the ``write()``
method which takes the string to output as the first parameter:

.. literalinclude:: cli_library/009.php

You can change the color of the text by passing in a color name as the second parameter:

.. literalinclude:: cli_library/010.php

This could be used to differentiate messages by status, or create 'headers' by using a different color. You can
even set background colors by passing the color name in as the third parameter:

.. literalinclude:: cli_library/011.php

The following foreground colors are available:

* black
* dark_gray
* blue
* dark_blue
* light_blue
* green
* light_green
* cyan
* light_cyan
* red
* light_red
* purple
* light_purple
* light_yellow
* yellow
* light_gray
* white

And a smaller number are available as background colors:

* black
* blue
* green
* cyan
* red
* yellow
* light_gray
* magenta

print()
=======

Print functions identically to the ``write()`` method, except that it does not force a newline either before or after.
Instead it prints it to the screen wherever the cursor is currently. This allows you to print multiple items all on
the same line, from different calls. This is especially helpful when you want to show a status, do something, then
print "Done" on the same line:

.. literalinclude:: cli_library/012.php

.. _cli-library-color:

color()
=======

While the ``write()`` command will write a single line to the terminal, ending it with a EOL character, you can
use the ``color()`` method to make a string fragment that can be used in the same way, except that it will not force
an EOL after printing. This allows you to create multiple outputs on the same row. Or, more commonly, you can use
it inside of a ``write()`` method to create a string of a different color inside:

.. literalinclude:: cli_library/013.php

This example would write a single line to the window, with ``fileA`` in yellow, followed by a tab, and then
``/path/to/file`` in white text.

error()
=======

If you need to output errors, you should use the appropriately named ``error()`` method. This writes light-red text
to STDERR, instead of STDOUT, like ``write()`` and ``color()`` do. This can be useful if you have scripts watching
for errors so they don't have to sift through all of the information, only the actual error messages. You use it
exactly as you would the ``write()`` method:

.. literalinclude:: cli_library/014.php

wrap()
======

This command will take a string, start printing it on the current line, and wrap it to a set length on new lines.
This might be useful when displaying a list of options with descriptions that you want to wrap in the current
window and not go off screen:

.. literalinclude:: cli_library/015.php

By default, the string will wrap at the terminal width. Windows currently doesn't provide a way to determine
the window size, so we default to 80 characters. If you want to restrict the width to something shorter that
you can be pretty sure fits within the window, pass the maximum line-length as the second parameter. This
will break the string at the nearest word barrier so that words are not broken.

.. literalinclude:: cli_library/016.php

You may find that you want a column on the left of titles, files, or tasks, while you want a column of text
on the right with their descriptions. By default, this will wrap back to the left edge of the window, which
doesn't allow things to line up in columns. In cases like this, you can pass in a number of spaces to pad
every line after the first line, so that you will have a crisp column edge on the left:

.. literalinclude:: cli_library/017.php

Would create something like this:

.. code-block:: none

    task1a     Lorem Ipsum is simply dummy
               text of the printing and
               typesetting industry.
    task1abc   Lorem Ipsum has been the
               industry's standard dummy
               text ever since the

newLine()
=========

The ``newLine()`` method displays a blank line to the user. It does not take any parameters:

.. literalinclude:: cli_library/018.php

clearScreen()
=============

You can clear the current terminal window with the ``clearScreen()`` method. In most versions of Windows, this will
simply insert 40 blank lines since Windows doesn't support this feature. Windows 10 bash integration should change
this:

.. literalinclude:: cli_library/019.php

showProgress()
==============

If you have a long-running task that you would like to keep the user updated with the progress, you can use the
``showProgress()`` method which displays something like the following:

.. code-block:: none

    [####......] 40% Complete

This block is animated in place for a very nice effect.

To use it, pass in the current step as the first parameter, and the total number of steps as the second parameter.
The percent complete and the length of the display will be determined based on that number. When you are done,
pass ``false`` as the first parameter and the progress bar will be removed.

.. literalinclude:: cli_library/020.php

table()
=======

.. literalinclude:: cli_library/021.php

.. code-block:: none

    +----+--------------------------+---------------------+--------+
    | ID | Title                    | Updated At          | Active |
    +----+--------------------------+---------------------+--------+
    | 7  | A great item title       | 2017-11-16 10:35:02 | 1      |
    | 8  | Another great item title | 2017-11-16 13:46:54 | 0      |
    +----+--------------------------+---------------------+--------+

wait()
======

Waits a certain number of seconds, optionally showing a wait message and
waiting for a key press.

.. literalinclude:: cli_library/022.php
