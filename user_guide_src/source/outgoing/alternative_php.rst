###################################
Alternate PHP Syntax for View Files
###################################

If you do not utilize a templating engine to simplify output,
you'll be using pure PHP in your
View files. To minimize the PHP code in these files, and to make it
easier to identify the code blocks it is recommended that you use PHPs
alternative syntax for control structures and short tag echo statements.
If you are not familiar with this syntax, it allows you to eliminate the
braces from your code, and eliminate "echo" statements.

Alternative Echos
=================

Normally to echo, or print out a variable you would do this::

    <?php echo $variable; ?>

With the alternative syntax you can instead do it this way::

    <?= $variable ?>

Alternative Control Structures
==============================

Controls structures, like if, for, foreach, and while can be written in
a simplified format as well. Here is an example using ``foreach``:

.. literalinclude:: alternative_php/001.php

Notice that there are no braces. Instead, the end brace is replaced with
``endforeach``. Each of the control structures listed above has a similar
closing syntax: ``endif``, ``endfor``, ``endforeach``, and ``endwhile``

Also notice that instead of using a semicolon after each structure
(except the last one), there is a colon. This is important!

Here is another example, using ``if``/``elseif``/``else``. Notice the colons:

.. literalinclude:: alternative_php/002.php
