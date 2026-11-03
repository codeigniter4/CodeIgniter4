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

// command line: php index.php users 21 profile --foo bar --option baz --option qux

echo $request->getOption('foo');       // bar
echo $request->getOption('not-there'); // null
echo $request->getOption('option');    // qux
```

!!! note "Note"
    Starting in v4.8.0, if the option you are trying to access is an array, this method will return the last value in the array. Use `getRawOption()` to get the full array of values for that option.

### getRawOption($key)

!!! success "Available from version 4.8.0"

Similar to `getOption()`, but returns the full array of values for the option if it is an array:

```php
<?php

// command line: php index.php users 21 profile --foo bar --option baz --option qux

echo $request->getRawOption('foo');         // bar
echo $request->getRawOption('not-there');   // null
var_dump($request->getRawOption('option')); // array(2) { [0]=> string(3) "baz" [1]=> string(3) "qux" }
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
