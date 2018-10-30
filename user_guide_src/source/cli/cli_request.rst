****************
CLIRequest Class
****************

If a request comes from a command line invocation, the request object is actually a
``CLIRequest``. It behaves the same as a :doc:`conventional request </incoming/request>`
but adds some accessor methods for convenience.

====================
Additional Accessors
====================

**getSegments()**

Returns an array of the command line arguments deemed to be part of a path::

    // command line: php index.php users 21 profile -foo bar
    echo $request->getSegments();  // ['users', '21', 'profile']

**getPath()**

Returns the reconstructed path as a string::

    // command line: php index.php users 21 profile -foo bar
    echo $request->getPath();  // users/21/profile

**getOptions()**

Returns an array of the command line arguments deemed to be options::

    // command line: php index.php users 21 profile -foo bar
    echo $request->getOptions();  // ['foo' => 'bar']

**getOption($which)**

Returns the value of a specific command line argument deemed to be an option::

    // command line: php index.php users 21 profile -foo bar
    echo $request->getOption('foo');  // bar
    echo $request->getOption('notthere'); // NULL

**getOptionString()**

Returns the reconstructed command line string for the options::

    // command line: php index.php users 21 profile -foo bar
    echo $request->getOptionPath();  // -foo bar
