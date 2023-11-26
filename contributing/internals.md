# CodeIgniter Internals Overview

This guide should help contributors understand how the core of the
framework works, and what needs to be done when creating new
functionality. Specifically, it details the information needed to create
new packages for the core.

## Dependencies

All packages should be designed to be completely isolated from the rest
of the packages, if possible. This will allow them to be used in
projects outside of CodeIgniter. Basically, this means that any
dependencies should be kept to a minimum. Any dependencies must be able
to be passed into the constructor. If you do need to use one of the
other core packages, you can create that in the constructor using the
`Services` class, as long as you provide a way for dependencies to
override that:

```php
    public function __construct(?Foo $foo = null)
    {
        $this->foo = $foo ?? \Config\Services::foo();
    }
```

## Type declarations

PHP7 provides [Type declarations](https://www.php.net/manual/en/language.types.declarations.php)
for method parameters and return types. Use it where possible. Return type
declaration is not always practical, but do try to make it work.

At this time, shipped CI4 production code does not use 
[Strict typing](https://www.php.net/manual/en/language.types.declarations.php#language.types.declarations.strict), 
and will not be any time soon. However, in the development phase, 
there are internal classes (in `utils/`) that are strictly typed.

## Abstractions

The amount of abstraction required to implement a solution should be the
minimal amount required. Every layer of abstraction brings additional
levels of technical debt and unnecessary complexity. That said, don't be
afraid to use it when it's needed and can help things.

-   Don't create a new container class when an array will do just fine.
-   Start simple, refactor as necessary to achieve clean separation of
    code, but don't overdo it.

## Testing

Any new packages submitted to the framework must be accompanied by unit
tests. The target is 80%+ code coverage of all classes within the
package.

-   Test only public methods, not protected and private unless the
    method really needs it due to complexity.
-   Don't just test that the method works, but test for all fail states,
    thrown exceptions, and other pathways through your code.

You should be aware of the extra assertions that we have made,
provisions for accessing private properties for testing, and mock
services. We have also made a **CITestStreamFilter** to capture test
output. Do check out similar tests in `tests/system/`, and read the
"Testing" section in the user guide, before you dig in to your own.

Some testing needs to be done in a separate process, in order to setup
the PHP globals to mimic test situations properly. See
`tests/system/HTTP/ResponseSendTest` for an example of this.

## Namespaces and Files

All new packages should live under the `CodeIgniter` namespace. The
package itself will need its own sub-namespace that collects all related
files into one grouping, like `CodeIgniter\HTTP`.

Files MUST be named the same as the class they hold, and they must match
the [Style Guide](styleguide.md), meaning CamelCase class and
file names. They should be in their own directory that matches the
sub-namespace under the **system** directory.

Take the Router class as an example. The Router lives in the
`CodeIgniter\Router` namespace. Its two main classes,
**RouteCollection** and **Router**, are in the files
**system/Router/RouteCollection.php** and **system/Router/Router.php**
respectively.

## Interfaces

Most base classes should have an interface defined for them. At the very
least this allows them to be easily mocked and passed to other classes
as a dependency, without breaking the type-hinting. The interface names
should match the name of the class with "Interface" appended to it, like
`RouteCollectionInterface`.

The Router package mentioned above includes the
`CodeIgniter\Router\RouteCollectionInterface` and
`CodeIgniter\Router\RouterInterface` interfaces to provide the
abstractions for the two classes in the package.

## Handlers

When a package supports multiple "drivers", the convention is to place
them in a **Handlers** directory, and name the child classes as
Handlers. You will often find that creating a `BaseHandler`, that the
child classes can extend, to be beneficial in keeping the code DRY.

See the Log and Session packages for examples.

## Configuration

Should the package require user-configurable settings, you should create
a new file just for that package under **app/Config**. The file name
should generally match the package name.

## Autoloader

All files within the package should be added to
**system/Config/AutoloadConfig.php**, in the "classmap" property. This
is only used for core framework files, and helps to minimize file system
scans and keep performance high.

## Command-Line Support

CodeIgniter has never been known for its strong CLI support. However,
if your package could benefit from it, create a new file under
**system/Commands**.

## Documentation

All packages must contain appropriate documentation that matches the
tone and style of the rest of the user guide. In most cases, the top
portion of the package's page should be treated in tutorial fashion,
while the second half would be a class reference.

## Modification of the `env` file

CodeIgniter is shipped with a template `env` file to support adding
secrets too sensitive to be stored in a version control system.
Contributors adding new entries to the env file should always ensure
that these entries are commented, i.e., starting with a hash (`#`). This
is because we have spark commands that actually copy the template file
to a `.env` file (which is actually the live version actually read by
CodeIgniter for secrets) if the latter is missing. As much as possible,
we do not want settings to go live unexpectedly without the user's
knowledge.
