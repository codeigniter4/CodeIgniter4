# Testing Packages

!!! success "Available from version 4.8.0"

A package that supplements CodeIgniter has no **app/**, **public/**, **writable/** or **spark** of its own. **AbstractOrchestrator** builds a throwaway application skeleton from the framework that your package is installed with, so that your tests run against real paths, configuration and commands instead of stubs.

## Defining an Orchestrator

Extend `CodeIgniter\Test\Orchestrator\AbstractOrchestrator` and implement `getPackageRoot()`. The following layout shows where the files of a package go:

```text
acme-blog/                           ← package root, returned by getPackageRoot()
├── build/                           ← generated, add to .gitignore
│   └── orchestrator/                ← the skeleton, getPath()
├── src/
├── tests/
│   ├── bootstrap.php                ← calls (new BlogOrchestrator())->boot()
│   ├── BlogOrchestrator.php         ← your AbstractOrchestrator subclass
│   ├── Fixtures/                    ← listed in getFixtures()
│   │   └── app/Config/Routes.php    ← mirrored to build/orchestrator/app/Config/Routes.php
│   └── Commands/PublishTest.php     ← tests that call spark()
├── vendor/                          ← shared with the skeleton
└── phpunit.dist.xml                 ← bootstrap="tests/bootstrap.php"
```

The orchestrator class is then:

```php
<?php

// tests/BlogOrchestrator.php

namespace Acme\Blog\Tests;

use CodeIgniter\Test\Orchestrator\AbstractOrchestrator;

final class BlogOrchestrator extends AbstractOrchestrator
{
    protected function getPackageRoot(): string
    {
        return dirname(__DIR__);
    }

    protected function getFixtures(): array
    {
        return [__DIR__ . '/Fixtures'];
    }

    protected function getEnv(): array
    {
        return ['app.baseURL' => 'http://localhost/'];
    }
}
```

The package root is the directory holding **vendor/autoload.php**. The skeleton needs a **vendor/autoload.php** of its own, because the framework loads `ROOTPATH . 'vendor/autoload.php'`. The orchestrator writes one that only requires the package's autoloader, so your package and its dev dependencies are available inside the skeleton without a second Composer install.

The following methods can be overridden:

- `getFixtures()` returns a list of directories that are mirrored onto the root of the skeleton, in order, after the framework files. A later directory overwrites an earlier one. Use them to add controllers, routes, configuration or a `Registrar`, for example **Fixtures/app/Config/Routes.php**. A fixture **app/Config/Paths.php** must keep the default `$systemDirectory`, which `build()` then points at the framework.
- `getEnv()` returns settings for the skeleton's **.env** file. Values are quoted, so they may contain spaces, `#` and quotes, but a value with a line break is refused. Names may only contain letters, digits, `.`, `_`, `-` and `\`, which covers forms such as `app.baseURL` and `Config\App.baseURL`. `CI_ENVIRONMENT` defaults to `development`, because `spark` does not run in the `testing` environment.
- `getFrameworkPath()` returns the framework that the skeleton is copied from, which is the one that provides the orchestrator. Expose it from your subclass if your tests need the framework's location.
- `afterBuild()` runs when the skeleton is complete, for example to create a database.
- `getPath()` returns the skeleton's location, **build/orchestrator** in the package root by default. A token is appended to it so that parallel processes do not share a skeleton. The token is read from `ORCHESTRATOR_TEST_TOKEN`, or else from `TEST_TOKEN`, which parallel runners such as ParaTest set for each process. It may only contain letters, digits, hyphens and underscores: an invalid `ORCHESTRATOR_TEST_TOKEN` throws an exception, and an invalid `TEST_TOKEN` is ignored. Set `ORCHESTRATOR_TEST_TOKEN` to an empty string to opt out of `TEST_TOKEN`.

!!! note "Note"
    The skeleton is generated, so add its location to your **.gitignore**, which is **build/** unless `getPath()` is overridden.

!!! note "Note"
    The skeleton is copied from the framework installed with your package, so only that version is tested. To build it from another version, keep a copy of that framework in a separate directory and return its path from `getFrameworkPath()`.

## Testing in the PHPUnit Process

Call `boot()` from your PHPUnit bootstrap file. It builds the skeleton, defines the path constants against it, and loads the framework's **system/Test/bootstrap.php**:

```php
<?php

// tests/bootstrap.php

use Acme\Blog\Tests\BlogOrchestrator;

require_once __DIR__ . '/../vendor/autoload.php';

(new BlogOrchestrator())->boot();
```

Point `bootstrap` at this file in your **phpunit.dist.xml**. Since it replaces Composer's autoloader as the bootstrap, it loads **vendor/autoload.php** from the package root itself, so that your orchestrator class can be found. Your tests then extend `CIUnitTestCase` as usual.

!!! important "Important"
    The path constants and the framework's static state last for the whole process, so one process hosts one skeleton.

## Testing Through spark

`spark()` runs the skeleton's **spark** in a separate process and returns a `SparkResult` with the `exitCode`, `stdout`, `stderr` and `timedOut` properties:

```php
// tests/Commands/PublishTest.php

$orchestrator = new BlogOrchestrator();
$orchestrator->build();

$result = $orchestrator->spark(['blog:publish', '--dry-run'], stdin: "yes\n", environment: ['APP_DEBUG' => '1']);

$this->assertSame(0, $result->exitCode);
$this->assertStringContainsString('Published', $result->stdout);
```

`stdin` is written to the process, and `environment` adds to the inherited environment. A `null` value unsets a variable. Pass `timeout` in seconds to terminate a process that runs longer, in which case `timedOut` is `true`.

!!! warning "Warning"
    A terminated process usually reports `-1` as its `exitCode`, so check `timedOut` first. Only the `spark` process is terminated, so processes that it started itself may keep running.

`stdin` is not a terminal, which affects interactive commands:

- Each line of `stdin` answers the next prompt asked from `execute()`, such as `ask()`. `select()` and `multiselect()` fall back to numbered choices, so pass the number instead of key presses.
- The command runs as non-interactive, so `interact()` and the prompts for missing arguments are skipped. Pass those values as arguments and options instead.
- Some prompt labels are not written to `stdout`, so assert on the command's own output instead.

!!! warning "Warning"
    Once `stdin` runs out, the remaining prompts take their defaults instead of failing, so a missing answer does not stop the command.

While debugging, you can also run the skeleton's **spark** yourself from the package root:

```console
php build/orchestrator/spark list
```

## Changing the Skeleton

`write()` creates a file in the skeleton, and `delete()` removes a file or a directory tree from it. Both take a path relative to the skeleton, and refuse a path that would leave it, whether through `..` or a symlink. `build()` removes the previous skeleton before it creates a new one, and `clean()` removes the skeleton, for example from `tearDownAfterClass()`. A skeleton booted with `boot()` is read until the process ends, so it is left in place.

## Worker Mode

The skeleton has no [Worker Mode](../installation/worker_mode.md) files of its own, because `worker:install` generates them. Run it from `afterBuild()` to publish **public/frankenphp-worker.php** and the **Caddyfile** from the template of the framework that the skeleton is copied from:

```php
protected function afterBuild(): void
{
    $this->spark(['worker:install']);
}
```

Your tests can then inspect or change the published files with `write()` and `delete()`.

!!! note "Note"
    The orchestrator does not run FrankenPHP. `spark()` waits for its process to exit, so it cannot host a server, and `boot()` sets up the regular per-process test environment instead of the worker's request loop. To send real requests to a worker, start `frankenphp run` yourself with the skeleton as its working directory, and stop it when your tests finish.
