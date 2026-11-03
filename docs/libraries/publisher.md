# Publisher

The Publisher library provides a means to copy files within a project using robust detection and error checking.

## Loading the Library

Because Publisher instances are specific to their source and destination, this library is not available through `Services` but should be instantiated or extended directly. E.g.:

```php
<?php

$publisher = new \CodeIgniter\Publisher\Publisher();
```

## Concept and Usage

`Publisher` solves a handful of common problems when working within a backend framework:

- How do I maintain project assets with version dependencies?
- How do I manage uploads and other "dynamic" files that need to be web-accessible?
- How can I update my project when the framework or modules change?
- How can components inject new content into existing projects?

At its most basic, publishing amounts to copying a file or files into a project. `Publisher` extends `FileCollection` to enact fluent-style command chaining to read, filter, and process input files, then copies or merges them into the target destination. You may use `Publisher` on demand in your Controllers or other components, or you may stage publications by extending the class and leveraging its discovery with `spark publish`.

### On Demand

Access `Publisher` directly by instantiating a new instance of the class:

```php
<?php

$publisher = new \CodeIgniter\Publisher\Publisher();
```

By default, the source and destination will be set to `ROOTPATH` and `FCPATH`, respectively, giving `Publisher` easy access to take any file from your project and make it web-accessible. Alternatively, you may pass a new source or source and destination into the constructor:

```php
<?php

use CodeIgniter\Publisher\Publisher;

$vendorPublisher = new Publisher(ROOTPATH . 'vendor');
$filterPublisher = new Publisher('/path/to/module/Filters', APPPATH . 'Filters');

// Once the source and destination are set you may start adding relative input files
$frameworkPublisher = new Publisher(ROOTPATH . 'vendor/codeigniter4/codeigniter4');

// All "path" commands are relative to $source
$frameworkPublisher->addPath('app/Config/Cookie.php');

// You may also add from outside the source, but the files will not be merged into subdirectories
$frameworkPublisher->addFiles([
    '/opt/mail/susan',
    '/opt/mail/ubuntu',
]);
$frameworkPublisher->addDirectory(SUPPORTPATH . 'Images');
```

Once all the files are staged, use one of the output commands (**copy()** or **merge()**) to process the staged files to their destination(s):

```php
<?php

// Place all files into $destination
$frameworkPublisher->copy();

// Place all files into $destination, overwriting existing files
$frameworkPublisher->copy(true);

// Place files into their relative $destination directories, overwriting and saving the boolean result
$result = $frameworkPublisher->merge(true);
```

See the [Library Reference](#library-reference) for a full description of available methods.

### Automation and Discovery

You may have regular publication tasks embedded as part of your application deployment or upkeep. `Publisher` leverages the powerful `Autoloader` to locate any child classes primed for publication:

```php
<?php

use CodeIgniter\CLI\CLI;
use CodeIgniter\Publisher\Publisher;

foreach (Publisher::discover() as $publisher) {
    $result = $publisher->publish();

    if ($result === false) {
        CLI::error($publisher::class . ' failed to publish!', 'red');
    }
}
```

By default, `discover()` will search for the "Publishers" directory across all namespaces, but you may specify a different directory and it will return any child classes found:

```php
<?php

use CodeIgniter\Publisher\Publisher;

$memePublishers = Publisher::discover('CatGIFs');
```

Most of the time you will not need to handle your own discovery. Just use the provided "publish" command:

```console
php spark publish
```

By default, on your class extension, `publish()` will add all files from your `$source` and merge them out to your destination, overwriting on collision.

#### Discovery in a specific namespace

!!! success "Available from version 4.6.0"

Since v4.6.0, you can also scan a specific namespace. This not only reduces the number of files to be scanned, but also avoids the need to rerun a Publisher. All you need to do is specify the desired root namespace in the second parameter of the `discover()` method.

```php
<?php

use CodeIgniter\Publisher\Publisher;

$memePublishers = Publisher::discover('Publishers', 'Namespace\Vendor\Package');
```

The specified namespace must be known to CodeIgniter. You can check the list of all namespaces using the "spark namespaces" command:

```console
php spark namespaces
```

The "publish" command also offers the `--namespace` option to define the namespace when searching for Publishers that might come from a library.

```console
php spark publish --namespace Namespace\Vendor\Package
```

### Security

In order to prevent modules from injecting malicious code into your projects, `Publisher` contains a config file that defines which directories and file patterns are allowed as destinations. By default, files may only be published to your project (to prevent access to the rest of the filesystem), and the **public/** folder (`FCPATH`) will only receive files with the following extensions:

- Web assets: css, scss, js, map
- Non-executable web files: htm, html, xml, json, webmanifest
- Fonts: ttf, eot, woff, woff2
- Images: gif, jpg, jpeg, tif, tiff, png, webp, bmp, ico, svg

If you need to add or adjust the security for your project, then alter the `$restrictions` property of `Config\Publisher` in **app/Config/Publisher.php**.

## Examples

Here are a handful of example use cases and their implementations to help you get started publishing.

### File Sync Example

You want to display a "photo of the day" image on your homepage. You have a feed for daily photos, but you need to get the actual file into a browsable location in your project at **public/images/daily_photo.jpg**. You can set up a [Custom Command](../cli/cli_commands.md) that runs daily to handle this for you:

```php
<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\Publisher\Publisher;
use Throwable;

class DailyPhoto extends BaseCommand
{
    protected $group       = 'Publication';
    protected $name        = 'publish:daily';
    protected $description = 'Publishes the latest daily photo to the homepage.';

    public function run(array $params)
    {
        $publisher = new Publisher('/path/to/photos/', FCPATH . 'assets/images');

        try {
            $publisher->addPath('daily_photo.jpg')->copy(true); // `true` to enable overwrites
        } catch (Throwable $e) {
            $this->showError($e);
        }
    }
}
```

Now running `spark publish:daily` will keep your homepage's image up to date. What if the photo is coming from an external API? You can use `addUri()` in place of `addPath()` to download the remote resource and publish it out instead:

```php
<?php

$publisher->addUri('https://example.com/feeds/daily_photo.jpg')->copy(true);
```

### Asset Dependencies Example

You want to integrate the frontend library "Bootstrap" into your project, but the frequent updates make it a hassle to keep up with. You can create a publication definition in your project to sync frontend assets by extending `Publisher` in your project. So **app/Publishers/BootstrapPublisher.php** might look like this:

```php
<?php

namespace App\Publishers;

use CodeIgniter\Publisher\Publisher;

class BootstrapPublisher extends Publisher
{
    /**
     * Tell Publisher where to get the files.
     * Since we will use Composer to download
     * them we point to the "vendor" directory.
     *
     * @var string
     */
    protected $source = VENDORPATH . 'twbs/bootstrap/';

    /**
     * FCPATH is always the default destination,
     * but we may want them to go in a sub-folder
     * to keep things organized.
     *
     * @var string
     */
    protected $destination = FCPATH . 'bootstrap';

    /**
     * Use the "publish" method to indicate that this
     * class is ready to be discovered and automated.
     */
    public function publish(): bool
    {
        return $this
            // Add all the files relative to $source
            ->addPath('dist')

            // Indicate we only want the minimized versions
            ->retainPattern('*.min.*')

            // Merge-and-replace to retain the original directory structure
            ->merge(true);
    }
}
```

!!! note "Note"
    The `$destination` directory must be created before executing the command.

Now add the dependency via Composer and call `spark publish` to run the publication:

```console
composer require twbs/bootstrap
php spark publish
```

... and you'll end up with something like this:

```
public/.htaccess
public/favicon.ico
public/index.php
public/robots.txt
public/
    bootstrap/
        css/
            bootstrap.min.css
            bootstrap-utilities.min.css.map
            bootstrap-grid.min.css
            bootstrap.rtl.min.css
            bootstrap.min.css.map
            bootstrap-reboot.min.css
            bootstrap-utilities.min.css
            bootstrap-reboot.rtl.min.css
            bootstrap-grid.min.css.map
        js/
            bootstrap.esm.min.js
            bootstrap.bundle.min.js.map
            bootstrap.bundle.min.js
            bootstrap.min.js
            bootstrap.esm.min.js.map
            bootstrap.min.js.map
```

### Module Deployment Example

You want to give developers using your popular authentication module the ability to expand on the default behavior of your Migration, Controller, and Model. You can create your own module "publish" command to inject these components into an application for use:

```php
<?php

namespace Math\Auth\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\Publisher\Publisher;
use Throwable;

class AuthPublish extends BaseCommand
{
    protected $group       = 'Auth';
    protected $name        = 'auth:publish';
    protected $description = 'Publish Auth components into the current application.';

    public function run(array $params)
    {
        // Use the Autoloader to figure out the module path
        $source = service('autoloader')->getNamespace('Math\\Auth')[0];

        $publisher = new Publisher($source, APPPATH);

        try {
            // Add only the desired components
            $publisher->addPaths([
                'Controllers',
                'Database/Migrations',
                'Models',
            ])->merge(false); // Be careful not to overwrite anything
        } catch (Throwable $e) {
            $this->showError($e);

            return;
        }

        // If publication succeeded then update namespaces
        foreach ($publisher->getPublished() as $file) {
            // Replace the namespace
            $contents = file_get_contents($file);
            $contents = str_replace('namespace Math\\Auth', 'namespace ' . APP_NAMESPACE, $contents);
            file_put_contents($file, $contents);
        }
    }
}
```

Now when your module users run `php spark auth:publish` they will have the following added to their project:

```
app/Controllers/AuthController.php
app/Database/Migrations/2017-11-20-223112_create_auth_tables.php
app/Models/LoginModel.php
app/Models/UserModel.php
```

## Library Reference

!!! note "Note"
    `Publisher` is an extension of [FileCollection](file_collections.md), so it has access to all those methods for reading and filtering files.

### Support Methods

#### [static] discover(string $directory = 'Publishers', string $namespace = ''): Publisher[]

Discovers and returns all Publishers in the specified namespace directory. For example, if both **app/Publishers/FrameworkPublisher.php** and **myModule/src/Publishers/AssetPublisher.php** exist and are extensions of `Publisher`, then `Publisher::discover()` would return an instance of each.

#### publish(): bool

Processes the full input-process-output chain. By default, this is the equivalent of calling `addPath($source)` and `merge(true)`, but child classes will typically provide their own implementation. `publish()` is called on all discovered Publishers when running `spark publish`. Returns success or failure.

#### getScratch(): string

Returns the temporary workspace, creating it if necessary. Some operations use intermediate storage to stage files and changes, and this provides the path to a transient, writable directory that you may use as well.

#### getErrors(): array&lt;string, Throwable>

Returns any errors from the last write operation. The array keys are the files that caused the error, and the values are the Throwable that was caught. Use `getMessage()` on the Throwable to get the error message.

#### addPath(string $path, bool $recursive = true)

Adds all files indicated by the relative path. The path is a reference to actual files or directories relative to `$source`. If the relative path resolves to a directory then `$recursive` will include sub-directories.

#### addPaths(array $paths, bool $recursive = true)

Adds all files indicated by the relative paths. The paths are references to actual files or directories relative to `$source`. If a relative path resolves to a directory then `$recursive` will include sub-directories.

#### addUri(string $uri)

Downloads the contents of a URI using `CURLRequest` into the scratch workspace, then adds the resulting file to the list.

#### addUris(array $uris)

Downloads the contents of URIs using `CURLRequest` into the scratch workspace, then adds the resulting files to the list.

!!! note "Note"
    The CURL request made is a simple `GET` and uses the response body for the file contents. Some remote files may need a custom request to be handled properly.

### Outputting Files

#### wipe()

Removes all files, directories, and sub-directories from `$destination`.

!!! important "Important"
    Use wisely.

#### copy(bool $replace = true): bool

Copies all files into the `$destination`. This does not recreate the directory structure, so every file from the current list will end up in the same destination directory. Using `$replace` will cause files to overwrite when there is already an existing file. Returns success or failure. Use `getPublished()` and `getErrors()` to troubleshoot failures. Be mindful of duplicate basename collisions, for example:

```php
<?php

use CodeIgniter\Publisher\Publisher;

$publisher = new Publisher('/home/source', '/home/destination');
$publisher->addPaths([
    'pencil/lead.png',
    'metal/lead.png',
]);

// This is bad! Only one file will remain at /home/destination/lead.png
$publisher->copy(true);
```

#### merge(bool $replace = true): bool

Copies all files into the `$destination` in appropriate relative sub-directories. Any files that match `$source` will be placed into their equivalent directories in `$destination`, effectively creating a "mirror" or "rsync" operation. Using `$replace` will cause files to overwrite when there is already an existing file. Since directories are merged, this will not affect other files in the destination. Returns success or failure. Use `getPublished()` and `getErrors()` to troubleshoot failures.

Example:

```php
<?php

use CodeIgniter\Publisher\Publisher;

$publisher = new Publisher('/home/source', '/home/destination');
$publisher->addPaths([
    'pencil/lead.png',
    'metal/lead.png',
]);

// Results in "/home/destination/pencil/lead.png" and "/home/destination/metal/lead.png"
$publisher->merge();
```

### Modifying Files

#### replace(string $file, array $replaces): bool

!!! success "Available from version 4.3.0"

Replaces the `$file` contents. The second parameter `$replaces` array specifies the search strings as keys and the replacements as values.

```php
<?php

use CodeIgniter\Publisher\Publisher;

$source    = service('autoloader')->getNamespace('CodeIgniter\\Shield')[0];
$publisher = new Publisher($source, APPPATH);

$file = APPPATH . 'Config/Auth.php';

$publisher->replace(
    $file,
    [
        'use CodeIgniter\Config\BaseConfig;' . "\n" => '',
        'class App extends BaseConfig'              => 'class App extends \Some\Package\SomeConfig',
    ],
);
```

#### addLineAfter(string $file, string $line, string $after): bool

!!! success "Available from version 4.3.0"

Adds `$line` after a line with the specific string `$after`.

```php
<?php

use CodeIgniter\Publisher\Publisher;

$source    = service('autoloader')->getNamespace('CodeIgniter\\Shield')[0];
$publisher = new Publisher($source, APPPATH);

$file = APPPATH . 'Config/App.php';

$publisher->addLineAfter(
    $file,
    '    public int $myOwnConfig = 1000;', // Adds this line
    'public bool $CSPEnabled = false;',     // After this line
);
```

#### addLineBefore(string $file, string $line, string $before): bool

!!! success "Available from version 4.3.0"

Adds `$line` before a line with the specific string `$before`.

```php
<?php

use CodeIgniter\Publisher\Publisher;

$source    = service('autoloader')->getNamespace('CodeIgniter\\Shield')[0];
$publisher = new Publisher($source, APPPATH);

$file = APPPATH . 'Config/App.php';

$publisher->addLineBefore(
    $file,
    '    public int $myOwnConfig = 1000;', // Add this line
    'public bool $CSPEnabled = false;',     // Before this line
);
```
