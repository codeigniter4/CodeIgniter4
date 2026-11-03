# CLI Library

CodeIgniter's CLI library makes creating interactive command-line scripts simple, including:

- Prompting the user for more information
- Writing multi-colored text to the terminal
- Beeping (be nice!)
- Showing progress bars during long tasks
- Wrapping long text lines to fit the window

## Initializing the Class

You do not need to create an instance of the CLI library, since all of its methods are static. Instead, you simply need to ensure your controller can locate it via a `use` statement above your class:

```php
<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class MyCommand extends BaseCommand
{
    // ...

    public function run(array $params)
    {
        // ...
    }
}
```

The class is automatically initialized when the file is loaded the first time.

## Getting Input from the User

Sometimes you need to ask the user for more information. They might not have provided optional command-line arguments, or the script may have encountered an existing file and needs confirmation before overwriting. This is handled with the `prompt()` or `promptByKey()` method.

!!! note "Note"
    Since v4.3.0, you can write tests for these methods with `PhpStreamWrapper`. See [Testing CLI Input](../testing/cli.md#testing-cli-input).

### prompt()

You can provide a question by passing it in as the first parameter:

```php
<?php

use CodeIgniter\CLI\CLI;

$color = CLI::prompt('What is your favorite color?');
```

You can provide a default answer that will be used if the user just hits enter by passing the default in the second parameter:

```php
<?php

use CodeIgniter\CLI\CLI;

$color = CLI::prompt('What is your favorite color?', 'blue');
```

You can restrict the acceptable answers by passing in an array of allowed answers as the second parameter:

```php
<?php

use CodeIgniter\CLI\CLI;

$overwrite = CLI::prompt('File exists. Overwrite?', ['y', 'n']);
```

Finally, you can pass [validation](../libraries/validation.md#validation) rules to the answer input as the third parameter:

```php
<?php

use CodeIgniter\CLI\CLI;

$email = CLI::prompt('What is your email?', null, 'required|valid_email');
```

Validation rules can also be written in the array syntax:

```php
<?php

use CodeIgniter\CLI\CLI;

$email = CLI::prompt('What is your email?', null, ['required', 'valid_email']);
```

### promptByKey()

Predefined answers (options) for prompt sometimes need to be described or are too complex to select via their value. `promptByKey()` allows the user to select an option by its key instead of its value:

```php
<?php

use CodeIgniter\CLI\CLI;

$fruit = CLI::promptByKey('These are your choices:', ['The red apple', 'The plump orange', 'The ripe banana']);
/*
 * These are your choices:
 *   [0]  The red apple
 *   [1]  The plump orange
 *   [2]  The ripe banana
 *
 * [0, 1, 2]:
 */
```

Named keys are also possible:

```php
<?php

use CodeIgniter\CLI\CLI;

$fruit = CLI::promptByKey(['These are your choices:', 'Which would you like?'], [
    'apple'  => 'The red apple',
    'orange' => 'The plump orange',
    'banana' => 'The ripe banana',
]);
/*
 * These are your choices:
 *   [apple]   The red apple
 *   [orange]  The plump orange
 *   [banana]  The ripe banana
 *
 * Which would you like? [apple, orange, banana]:
 */
```

Finally, you can pass [validation](../libraries/validation.md#validation) rules to the answer input as the third parameter. The acceptable answers are automatically restricted to the passed options.

### promptByMultipleKeys()

!!! success "Available from version 4.3.0"

This method is the same as `promptByKey()`, but it supports multiple values.

```php
<?php

use CodeIgniter\CLI\CLI;

$hobbies = CLI::promptByMultipleKeys('Select your hobbies:', ['Playing game', 'Sleep', 'Badminton']);
/*
 * Select your hobbies:
 *   [0]  Playing game
 *   [1]  Sleep
 *   [2]  Badminton
 *
 * You can specify multiple values separated by commas.
 * [0, 1, 2]:
 *
 * if your answer is '0,2', the return is the key and the value of the options :
 * [
 *   [0] => "Playing game",
 *   [2] => "Badminton"
 * ]
 */
```

!!! important "Important"
    The method `promptByMultipleKeys()`, unlike `promptByKey()`, does not support named keys or validation.

## Providing Feedback

### write()

Several methods are available for providing feedback to your users. This can be as simple as a single status update or a complex table of information that wraps to the user's terminal window. At the core of this is the `write()` method, which takes the string to output as the first parameter:

```php
<?php

use CodeIgniter\CLI\CLI;

CLI::write('The rain in Spain falls mainly on the plains.');
```

You can change the color of the text by passing in a color name as the second parameter:

```php
<?php

use CodeIgniter\CLI\CLI;

CLI::write('File created.', 'green');
```

This could be used to differentiate messages by status, or create 'headers' by using a different color. You can even set background colors by passing the color name in as the third parameter:

```php
<?php

use CodeIgniter\CLI\CLI;

CLI::write('File overwritten.', 'light_red', 'dark_gray');
```

The following foreground colors are available:

- black
- dark_gray
- blue
- dark_blue
- light_blue
- green
- light_green
- cyan
- light_cyan
- red
- light_red
- purple
- light_purple
- light_yellow
- yellow
- light_gray
- white

And a smaller number are available as background colors:

- black
- blue
- green
- cyan
- red
- yellow
- light_gray
- magenta

### print()

Print functions identically to the `write()` method, except that it does not force a newline either before or after. Instead, it prints to the screen wherever the cursor currently is. This allows you to print multiple items all on the same line, from different calls. This is especially helpful when you want to show a status, do something, then print "Done" on the same line:

```php
<?php

use CodeIgniter\CLI\CLI;

for ($i = 0; $i <= 10; $i++) {
    CLI::print($i);
}
```

### color()

While the `write()` command will write a single line to the terminal, ending it with an EOL character, you can use the `color()` method to make a string fragment that can be used in the same way, except that it will not force an EOL after printing. This allows you to create multiple outputs on the same row. Or, more commonly, you can use it inside of a `write()` method to create a string of a different color inside:

```php
<?php

use CodeIgniter\CLI\CLI;

CLI::write("fileA \t" . CLI::color('/path/to/file', 'white'), 'yellow');
```

This example would write a single line to the window, with `fileA` in yellow, followed by a tab, and then `/path/to/file` in white text.

### error()

If you need to output errors, you should use the appropriately named `error()` method. This writes light-red text to STDERR, instead of STDOUT, like `write()` and `color()` do. This can be useful if you have scripts watching for errors so they don't have to sift through all of the information, only the actual error messages. You use it exactly as you would the `write()` method:

```php
<?php

use CodeIgniter\CLI\CLI;

CLI::error('Cannot write to file: ' . $file);
```

### wrap()

This command will take a string, start printing it on the current line, and wrap it to a set length on new lines. This might be useful when displaying a list of options with descriptions that you want to wrap in the current window and not go off screen:

```php
<?php

use CodeIgniter\CLI\CLI;

CLI::color("task1\t", 'yellow');
CLI::wrap('Some long description goes here that might be longer than the current window.');
```

By default, the string will wrap at the terminal width. Windows currently doesn't provide a way to determine the window size, so we default to 80 characters. If you want to restrict the width to something shorter that you can be pretty sure fits within the window, pass the maximum line length as the second parameter. This will break the string at the nearest word barrier so that words are not broken.

```php
<?php

use CodeIgniter\CLI\CLI;

// Wrap the text at max 20 characters wide
CLI::wrap($description, 20);
```

You may find that you want a column on the left of titles, files, or tasks, while you want a column of text on the right with their descriptions. By default, this will wrap back to the left edge of the window, which doesn't allow things to line up in columns. In cases like this, you can pass in a number of spaces to pad every line after the first line, so that you will have a crisp column edge on the left:

```php
<?php

use CodeIgniter\CLI\CLI;

$titles = [
    'task1a',
    'task1abc',
];
$descriptions = [
    'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
    "Lorem Ipsum has been the industry's standard dummy text ever since the",
];

// Determine the maximum length of all titles
// to determine the width of the left column
$maxlen = max(array_map('strlen', $titles));

for ($i = 0; $i < count($titles); $i++) {
    CLI::write(
        // Display the title on the left of the row
        substr(
            $titles[$i] . str_repeat(' ', $maxlen + 3),
            0,
            $maxlen + 3,
        ) .
        // Wrap the descriptions in a right-hand column
        // with its left side 3 characters wider than
        // the longest item on the left.
        CLI::wrap($descriptions[$i], 40, $maxlen + 3),
    );
}
```

This would create something like this:

```none
task1a     Lorem Ipsum is simply dummy
           text of the printing and
           typesetting industry.
task1abc   Lorem Ipsum has been the
           industry's standard dummy
           text ever since the
```

### newLine()

The `newLine()` method displays a blank line to the user. It does not take any parameters:

```php
<?php

use CodeIgniter\CLI\CLI;

CLI::newLine();
```

### clearScreen()

You can clear the current terminal window with the `clearScreen()` method. On Windows terminals without virtual terminal support, this inserts 40 blank lines instead:

```php
<?php

use CodeIgniter\CLI\CLI;

CLI::clearScreen();
```

### showProgress()

If you have a long-running task and would like to keep the user updated on its progress, you can use the `showProgress()` method, which displays something like the following:

```none
[####......] 40% Complete
```

This block is animated in place for a very nice effect.

To use it, pass in the current step as the first parameter, and the total number of steps as the second parameter. The percent complete and the length of the display will be determined based on those numbers. When you are done, pass `false` as the first parameter and the progress bar will be removed.

```php
<?php

use CodeIgniter\CLI\CLI;

$totalSteps = count($tasks);
$currStep   = 1;

foreach ($tasks as $task) {
    CLI::showProgress($currStep++, $totalSteps);
    $task->run();
}

// Done, so erase it...
CLI::showProgress(false);
```

### table()

```php
<?php

use CodeIgniter\CLI\CLI;

$thead = ['ID', 'Title', 'Updated At', 'Active'];
$tbody = [
    [7, 'A great item title', '2017-11-15 10:35:02', 1],
    [8, 'Another great item title', '2017-11-16 13:46:54', 0],
];

CLI::table($tbody, $thead);
```

```none
+----+--------------------------+---------------------+--------+
| ID | Title                    | Updated At          | Active |
+----+--------------------------+---------------------+--------+
| 7  | A great item title       | 2017-11-16 10:35:02 | 1      |
| 8  | Another great item title | 2017-11-16 13:46:54 | 0      |
+----+--------------------------+---------------------+--------+
```

### wait()

Waits a certain number of seconds, optionally showing a wait message and waiting for a key press.

```php
<?php

use CodeIgniter\CLI\CLI;

// wait for specified interval, with countdown displayed
CLI::wait($seconds, true);

// show continuation message and wait for input
CLI::wait(0, false);

// wait for specified interval
CLI::wait($seconds, false);
```
