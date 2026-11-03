# CLIRequest Class

If a request comes from a command line invocation, the request object is actually a `CLIRequest`. It behaves the same as a [conventional request](../incoming/request.md) but adds some accessor methods for convenience.

## Additional Accessors

### getSegments()

Returns an array of the command line arguments deemed to be part of a path:

```php
<?php

// command line: php index.php users 21 profile --foo bar
echo $request->getSegments();  // ['users', '21', 'profile']
```

### getPath()

Returns the reconstructed path as a string:

```php
<?php

// command line: php index.php users 21 profile --foo bar
echo $request->getPath();  // users/21/profile
```

### getOptions()

Returns an array of the command line arguments deemed to be options:

```php
<?php

// command line: php index.php users 21 profile --foo bar
echo $request->getOptions();  // ['foo' => 'bar']
```

### getOption($key)

Returns the value of a specific command line argument deemed to be an option:

```php
<?php

// command line: php index.php users 21 profile --foo bar
echo $request->getOption('foo');      // bar
echo $request->getOption('notthere'); // null
```

### getOptionString()

Returns the reconstructed command line string for the options:

```php
<?php

// command line: php index.php users 21 profile --foo bar
echo $request->getOptionString();  // -foo bar
```

Passing `true` to the first argument will try to write long options using two dashes:

```php
<?php

// php index.php user 21 --foo bar -f
echo $request->getOptionString();     // -foo bar -f
echo $request->getOptionString(true); // --foo bar -f
```
