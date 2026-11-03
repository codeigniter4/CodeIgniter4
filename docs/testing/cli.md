# Testing CLI Commands

## Using MockInputOutput

!!! success "Available from version 4.5.0"

### MockInputOutput

**MockInputOutput** provides an easy way to write tests for commands that require user input, such as `CLI::prompt()`, `CLI::wait()`, and `CLI::input()`.

You can replace the `InputOutput` class with `MockInputOutput` during test execution to capture inputs and outputs.

!!! note "Note"
    When you use `MockInputOutput`, you don't need to use [StreamFilterTrait](#streamfiltertrait), [CITestStreamFilter](#citeststreamfilter), and [PhpStreamWrapper](#phpstreamwrapper).

#### Helper Methods

##### getOutput(?int $index = null): string

Gets the output.

- If you call it like `$io->getOutput()`, it returns the whole output string.
- If you specify `0` or a positive number, it returns the output array item. Each item has the output of a `CLI::fwrite()` call.
- If you specify a negative number `-n`, it returns the last `n`-th item of the output array.

##### getOutputs(): array

Returns the output array. Each item has the output of a `CLI::fwrite()` call.

### How to Use

- `CLI::setInputOutput()` can set the `MockInputOutput` instance to the `CLI` class.
- `CLI::resetInputOutput()` resets the `InputOutput` instance in the `CLI` class.
- `MockInputOutput::setInputs()` sets the user input array.
- `MockInputOutput::getOutput()` gets the command output.

The following code tests the command `spark db:table`:

```php
<?php

use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\Mock\MockInputOutput;

final class DbTableTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrateOnce = true;

    public function testDbTable(): void
    {
        // Set MockInputOutput to CLI.
        $io = new MockInputOutput();
        CLI::setInputOutput($io);

        // User will input "a" (invalid value) and "0".
        $io->setInputs(['a', '0']);

        command('db:table');

        // Get the whole output string.
        $output = $io->getOutput();

        $expected = 'Which table do you want to see? [0, 1, 2, 3, 4, 5, 6, 7, 8, 9]: a';
        $this->assertStringContainsString($expected, $output);

        $expected = 'Data of Table "db_migrations":';
        $this->assertStringContainsString($expected, $output);

        // Remove MockInputOutput.
        CLI::resetInputOutput();
    }
}
```

## Without MockInputOutput

### Testing CLI Output

#### StreamFilterTrait

!!! success "Available from version 4.3.0"

**StreamFilterTrait** provides an alternative to `MockInputOutput` for capturing CLI output.

You may need to test things that are difficult to test. Sometimes, capturing a stream, like PHP's own STDOUT or STDERR, might be helpful. The `StreamFilterTrait` helps you capture the output from the stream of your choice.

##### How to Use

- `StreamFilterTrait::getStreamFilterBuffer()` gets the captured data from the buffer.
- `StreamFilterTrait::resetStreamFilterBuffer()` resets captured data.

An example demonstrating this inside one of your test cases:

```php
<?php

namespace Tests;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;

final class SomeTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    public function testSomeOutput(): void
    {
        CLI::write('first.');

        $this->assertSame("\nfirst.\n", $this->getStreamFilterBuffer());

        $this->resetStreamFilterBuffer();

        CLI::write('second.');

        $this->assertSame("second.\n", $this->getStreamFilterBuffer());
    }
}
```

The `StreamFilterTrait` has a configurator that is called automatically. See [Testing Traits](overview.md#traits).

If you override the `setUp()` or `tearDown()` methods in your test, then you must call the `parent::setUp()` and `parent::tearDown()` methods respectively to configure the `StreamFilterTrait`.

#### CITestStreamFilter

**CITestStreamFilter** is for manual/single use.

If you need to capture streams in only one test, then instead of using the StreamFilterTrait trait, you can manually add a filter to streams.

##### How to Use

- `CITestStreamFilter::registration()` Filter registration.
- `CITestStreamFilter::addOutputFilter()` Adding a filter to the output stream.
- `CITestStreamFilter::addErrorFilter()` Adding a filter to the error stream.
- `CITestStreamFilter::removeOutputFilter()` Removing a filter from the output stream.
- `CITestStreamFilter::removeErrorFilter()` Removing a filter from the error stream.

```php
<?php

namespace Tests;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;

final class SomeTest extends CIUnitTestCase
{
    public function testSomeOutput(): void
    {
        CITestStreamFilter::registration();
        CITestStreamFilter::addOutputFilter();

        CLI::write('first.');

        CITestStreamFilter::removeOutputFilter();
    }
}
```

### Testing CLI Input

#### PhpStreamWrapper

!!! success "Available from version 4.3.0"

**PhpStreamWrapper** provides a way to write tests for methods that require user input, such as `CLI::prompt()`, `CLI::wait()`, and `CLI::input()`.

!!! note "Note"
    The PhpStreamWrapper is a stream wrapper class. If you don't know PHP's stream wrapper, see [The streamWrapper class](https://www.php.net/manual/en/class.streamwrapper.php) in the PHP manual.

##### How to Use

- `PhpStreamWrapper::register()` Register the `PhpStreamWrapper` to the `php` protocol.
- `PhpStreamWrapper::restore()` Restore the php protocol wrapper back to the PHP built-in wrapper.
- `PhpStreamWrapper::setContent()` Set the input data.

!!! important "Important"
    The PhpStreamWrapper is intended only for testing `php://stdin`. But when you register it, it handles all the [php protocol](https://www.php.net/manual/en/wrappers.php.php) streams, such as `php://stdout`, `php://stderr`, `php://memory`. So it is strongly recommended that `PhpStreamWrapper` be registered/unregistered only when needed. Otherwise, it will interfere with other built-in php streams while registered.

An example demonstrating this inside one of your test cases:

```php
<?php

namespace Tests;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\PhpStreamWrapper;

final class SomeTest extends CIUnitTestCase
{
    public function testPrompt(): void
    {
        // Register the PhpStreamWrapper.
        PhpStreamWrapper::register();

        // Set the user input to 'red'. It will be provided as `php://stdin` output.
        $expected = 'red';
        PhpStreamWrapper::setContent($expected);

        $output = CLI::prompt('What is your favorite color?');

        $this->assertSame($expected, $output);

        // Restore php protocol wrapper.
        PhpStreamWrapper::restore();
    }
}
```
