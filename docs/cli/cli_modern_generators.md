# Modern Generator Commands

!!! success "Available from version 4.8.0"

Generator commands are spark commands that create files from templates. The built-in `make:*` commands documented in [CLI Generators](cli_generators.md) are the canonical examples. The modern counterpart of the legacy `GeneratorTrait` is the `AbstractGeneratorCommand` base class paired with a `#[GeneratorCommand]` attribute: the attribute describes *what* to generate, and the base class carries the whole generation pipeline so that a trivial generator needs no method body at all.

!!! note "Note"
    `GeneratorTrait` is deprecated, but it continues to work for legacy `BaseCommand` generators. See [Migrating From GeneratorTrait](#migrating-from-generatortrait).

## Creating a Generator Command

A modern generator command is a [modern spark command](cli_modern_commands.md) that:

- extends `CodeIgniter\CLI\AbstractGeneratorCommand` (which itself extends `AbstractCommand`);
- declares the usual `#[Command]` attribute **and** a `#[GeneratorCommand]` attribute.

A minimal generator needs nothing else:

```php
<?php

namespace App\Commands;

use CodeIgniter\CLI\AbstractGeneratorCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\Attributes\GeneratorCommand;

#[Command(name: 'make:widget', description: 'Generates a new widget class.', group: 'Generators')]
#[GeneratorCommand(component: 'Widget', template: 'widget.tpl.php', directory: 'Widgets')]
class WidgetGenerator extends AbstractGeneratorCommand
{
}
```

Out of the box this command:

- declares a required `name` argument, and prompts for it when omitted on an interactive run (see [Prompting for Missing Arguments](cli_modern_commands.md#prompting-for-missing-arguments));
- declares the `--namespace` / `-n`, `--suffix` / `-s`, and `--force` / `-f` options on top of the framework defaults;
- ships a default `execute()` that renders the template and writes the class file, returning `EXIT_SUCCESS` or `EXIT_ERROR`.

### The `#[GeneratorCommand]` Attribute

The attribute holds the generation configuration:

- `component` is the component noun (`Controller`, `Model`, ...). It drives suffix handling: an input whose trailing component is spelled with the wrong case is normalized (`make:widget foowidget` generates `FooWidget`), and passing `--suffix` appends the component to inputs that do not already end with it.
- `template` is the basename of the fallback view under `CodeIgniter\Commands\Generators\Views` (see [Templates](#templates) for the lookup order).
- `directory` is the optional sub-namespace (and thus subdirectory) the class is generated into, e.g. `Widgets` places classes under `App\Widgets`.
- `classNameLang` is the language string key used as the prompt label when the `name` argument must be asked for interactively. Defaults to the generic `CLI.generator.className.default` label.
- `namespace` optionally pins the root namespace, ignoring the `--namespace` option.
- `sortImports` (default `true`) controls whether the first contiguous `use` block of the generated content is sorted alphabetically.

Like `#[Command]`, the attribute validates its inputs at construction time: `component` must be a class-name fragment (a letter followed by letters, digits, or underscores), `template` must not be empty, and `directory` and `namespace`, when given, must be valid backslash-separated namespace fragments.

## Templates

Templates are regular view files. Because they are rendered through `view()`, a template cannot open with a literal PHP tag, so the pipeline recognizes a few pseudo-placeholders that are substituted after rendering:

- `<@php` becomes `<?php`
- `{namespace}` becomes the namespace of the generated class
- `{class}` becomes the short class name

A minimal template:

```php
<@php

namespace {namespace};

class {class}
{
}
```

The view used for a run is resolved in this order:

1.  the `$templatePath` property, when a subclass assigns a full namespaced view path at runtime;
2.  the entry keyed by the command name in **app/Config/Generators.php**, when it is a string;
3.  the `template` declared on the attribute, looked up under `CodeIgniter\Commands\Generators\Views`.

To let users customize your template, register it in the config file:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Generators extends BaseConfig
{
    public array $views = [
        // ..
        'make:widget' => 'App\Commands\Generators\Views\widget.tpl.php',
    ];
}
```

## Generation Pipeline

The default `execute()` simply returns `$this->generateClass()`, which walks through these steps:

1.  The `name` argument is normalized: segments are converted to PascalCase, a miscased trailing component is fixed, and the component suffix is appended when applicable. The result is qualified against the root namespace and the attribute's `directory`. A result that is not a valid class name (for example, one containing `..` segments) aborts with `EXIT_ERROR`.
2.  The target path is derived from the autoloader's mapping for the root namespace. An unknown namespace aborts with `EXIT_ERROR` and an error message.
3.  The template is rendered with the view data, placeholders are replaced, and the imports are sorted when `sortImports` is enabled.
4.  Safety checks run before writing: generating into the `CodeIgniter` namespace asks for confirmation on interactive runs (and proceeds with a warning on non-interactive ones), and an existing target file aborts with `EXIT_ERROR` unless `--force` is passed.

Generators that produce more than one artifact can call `generateView()` for non-class files, and may reassign the mutable `$template` / `$templatePath` properties between generation calls.

## Customization Hooks

### Placeholders and View Data

Most real generators need more than `{namespace}` and `{class}`. Override `getReplacements()` to add placeholder substitutions and `getTemplateData()` to pass variables into the view:

```php
<?php

namespace App\Commands;

use CodeIgniter\CLI\AbstractGeneratorCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\Attributes\GeneratorCommand;
use CodeIgniter\CLI\Input\Option;

#[Command(name: 'make:widget', description: 'Generates a new widget class.', group: 'Generators')]
#[GeneratorCommand(component: 'Widget', template: 'widget.tpl.php', directory: 'Widgets')]
class WidgetGenerator extends AbstractGeneratorCommand
{
    protected function configure(): void
    {
        // Registers the required "name" argument.
        parent::configure();

        $this->addOption(new Option(
            name: 'table',
            description: 'Table name to use.',
            requiresValue: true,
            default: 'widgets',
        ));
    }

    protected function getReplacements(string $class): array
    {
        // Replaces the {table} placeholder in the template.
        return ['{table}' => (string) $this->getValidatedOption('table')];
    }

    protected function getTemplateData(string $class): array
    {
        // Available as a plain $sortable variable inside the template.
        return ['sortable' => true];
    }
}
```

Entries returned by `getReplacements()` take precedence over the core pairs, so a generator can even override how `{namespace}` or `{class}` is derived.

### Trimming Options and Forcing the Suffix

The common generator options are registered through `provideGeneratorOptions()`. Override it to drop options that make no sense for your generator, and override `shouldAppendSuffix()` when suffixing is not driven by the `--suffix` flag:

```php
<?php

namespace App\Commands;

use CodeIgniter\CLI\AbstractGeneratorCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\Attributes\GeneratorCommand;

#[Command(name: 'make:widget', description: 'Generates a new widget class.', group: 'Generators')]
#[GeneratorCommand(component: 'Widget', template: 'widget.tpl.php', directory: 'Widgets')]
class WidgetGenerator extends AbstractGeneratorCommand
{
    protected function provideGeneratorOptions(): void
    {
        // No --suffix: suffixing is not optional for this generator.
        $this->addNamespaceOption()->addForceOption();
    }

    protected function shouldAppendSuffix(): bool
    {
        // The "Widget" suffix is always appended.
        return true;
    }
}
```

The base class guards every read of `--suffix` and `--force`, so omitting them is safe.

### Other Hooks

- `basename(string $filename): string` changes the file basename before saving. Useful for components whose file name carries a date, like migrations.
- `getNamespace(): string` resolves the root namespace. The default returns the attribute's `namespace` or the `--namespace` option.
- `buildPath(string $class): string` maps the qualified class to a file path through the autoloader. Override for components with special file locations, like tests.
- `getBasePath(string $namespace): ?string` picks the directory registered for the namespace. The default takes the first autoloader entry. Override when the namespace maps to several directories and a specific one is wanted, like the `tests/` copy of `CodeIgniter`.
- `renderTemplate(array $data = []): string` renders the resolved view.

## Prompting for the Class Name

`AbstractGeneratorCommand` implements `PromptsForMissingInputInterface`, so running a generator interactively without a class name prompts for it instead of failing validation. The prompt label comes from the attribute's `classNameLang` language key. Non-interactive runs (piped input, `--no-interaction`) fail fast with the usual missing-arguments error.

The mechanism is available to every modern command, not just generators. See [Prompting for Missing Arguments](cli_modern_commands.md#prompting-for-missing-arguments).

## Migrating From `GeneratorTrait`

The base class covers everything the trait did, but the configuration moves from mutable properties and the `$params` array to the attribute and the validated input accessors.

**Configuration properties**

`protected $component`, `protected $template`, `protected $directory`, `protected $classNameLang`, `protected ?string $namespace`  
Move to the corresponding parameters of the `#[GeneratorCommand]` attribute.

`protected ?string $templatePath`  
Stays a runtime property on the base class. Assign it only when switching templates mid-run (multi-artifact generators).

`$this->setSortImports(false)`  
Becomes `sortImports: false` on the attribute.

`$this->setEnabledSuffixing(false)`  
Gone. Override `provideGeneratorOptions()` to not register `--suffix`, and `shouldAppendSuffix()` to control suffixing directly. Note that a user-passed `--suffix`, which the trait silently ignored, then fails validation as an unknown option.

`$this->setHasClassName(false)`  
Gone. Override `configure()` without calling `parent::configure()` and declare whatever arguments your generator actually needs. Also override `execute()`, since the default one calls `generateClass()`, which requires the `name` argument.

**Input handling**

`run(array $params)`  
Not used by modern commands. The default `execute()` already calls `generateClass()`. Override `execute()` only when the run involves more than one generation step.

`$params[0]`  
The class name is now the declared `name` argument: `$this->getValidatedArgument('name')`.

`$this->getOption('foo')` / `CLI::getOption('foo')`  
Become `$this->getValidatedOption('foo')`. Extra options must be declared in `configure()` (after calling `parent::configure()`).

Class-name prompting  
Automatic. The trait prompted inside its name normalization. The base class prompts through [Prompting for Missing Arguments](cli_modern_commands.md#prompting-for-missing-arguments) before binding, so the argument is guaranteed present by the time your code runs.

**Content hooks**

`prepare(string $class)` override  
Gone. Return extra placeholder pairs from `getReplacements()` and view data from `getTemplateData()` instead of calling `parseTemplate()` with arrays.

`basename(string $filename)` override  
Same hook name and signature on the base class.

`getNamespace()` / `buildPath()` overrides  
Same hook names and signatures on the base class.

A typical `GeneratorTrait` generator:

```php
<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\GeneratorTrait;

class WidgetGenerator extends BaseCommand
{
    use GeneratorTrait;

    protected $group       = 'Generators';
    protected $name        = 'make:widget';
    protected $description = 'Generates a new widget class.';
    protected $usage       = 'make:widget <name> [options]';
    protected $arguments   = ['name' => 'The widget class name.'];
    protected $options     = [
        '--namespace' => 'Set root namespace. Default: "APP_NAMESPACE".',
        '--suffix'    => 'Append the component title to the class name.',
        '--force'     => 'Force overwrite existing file.',
    ];

    public function run(array $params)
    {
        $this->component = 'Widget';
        $this->directory = 'Widgets';
        $this->template  = 'widget.tpl.php';

        $this->generateClass($params);

        return EXIT_SUCCESS;
    }
}
```

…becomes, as a modern generator command:

```php
<?php

namespace App\Commands;

use CodeIgniter\CLI\AbstractGeneratorCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\Attributes\GeneratorCommand;

#[Command(name: 'make:widget', description: 'Generates a new widget class.', group: 'Generators')]
#[GeneratorCommand(component: 'Widget', template: 'widget.tpl.php', directory: 'Widgets')]
class WidgetGenerator extends AbstractGeneratorCommand
{
}
```

Behavioral changes to be aware of when migrating:

- **Failures produce failing exit codes.** The trait's `generateClass()` returned `void`, so a generator reported `EXIT_SUCCESS` even when the target file already existed. The base class returns `EXIT_ERROR` for an existing file without `--force`, a write failure, and an undefined namespace. Declining the `CodeIgniter` namespace confirmation still exits with `EXIT_SUCCESS`.
- **The** `CodeIgniter` **namespace confirmation is asked once, and only on interactive runs.** The trait asked for every file. The base class asks once per `generateClass()` and reuses the answer for the views generated after it. Non-interactive runs print the warning and proceed instead of blocking on input that will never arrive.
- **The** `CodeIgniter` **namespace confirmation keys off the target path.** The trait compared the raw `--namespace` option, so an attribute-pinned `CodeIgniter` namespace or a spelling like `--namespace CodeIgniter/` did not warn. The base class warns exactly when the file would be written under `SYSTEMPATH`, so a `CodeIgniter\Foo` test class headed for `tests/system` is left alone.
- **Placeholder replacement is single-pass.** Replacements are applied with `strtr()`, so a replacement value that happens to contain another placeholder is no longer substituted again.

## AbstractGeneratorCommand

## CodeIgniter\CLI

### AbstractGeneratorCommand

All hooks below are `protected`: they are called or overridden from within your own generator, never from the outside.

#### generateClass(): int

Runs the full pipeline for the qualified class name and returns an `EXIT_*` status. This is what the default `execute()` calls.

#### generateView(string $view): int

- **Parameters**  
    - **$view** `string` Namespaced view name to generate.

Like `generateClass`, but for non-class artifacts. The name is used as-is, without qualification or suffix handling.

#### isCodeIgniterNamespaceDeclined(): bool

Whether the `CodeIgniter` namespace confirmation was declined since the last `generateClass` call. A declined run still returns `EXIT_SUCCESS`, so check this before generating anything that depends on the declined file.

#### getReplacements(string $class): array

- **Parameters**  
    - **$class** `string` The namespaced class (or view) being generated.

Returns extra placeholder replacements. Entries take precedence over the core `{namespace}` / `{class}` pairs. Defaults to `[]`.

#### getTemplateData(string $class): array

- **Parameters**  
    - **$class** `string` The namespaced class (or view) being generated.

Returns view data passed to the template when rendering. Defaults to `[]`.

#### shouldAppendSuffix(): bool

Whether the component suffix should be appended to the class name. The default reads the `--suffix` flag when it is declared.

#### basename(string $filename): string

- **Parameters**  
    - **$filename** `string` The computed target file path.

Returns the file basename to save under. Override to decorate it, for example with a timestamp.

#### getNamespace(): string

Resolves the root namespace from the attribute override or the `--namespace` option.

#### buildPath(string $class): string

- **Parameters**  
    - **$class** `string` The namespaced class (or view) being generated.

Maps the class to its target file path through the autoloader. Returns an empty string (after printing an error) when the namespace is not registered.

#### getBasePath(string $namespace): ?string

- **Parameters**  
    - **$namespace** `string` The resolved root namespace.

Returns the directory registered for the namespace, or `null` when it is not registered. The default takes the first autoloader entry.

#### renderTemplate(array $data = []): string

- **Parameters**  
    - **$data** `array` View data for the template.

Renders the resolved generator view (see [Templates](#templates) for the resolution order).

#### provideGeneratorOptions(): void

Registers the common generator options. The default registers all three of `--namespace`, `--suffix`, and `--force` through the `final` helpers `addNamespaceOption`, `addSuffixOption`, and `addForceOption`. Override to register a subset.

#### addNamespaceOption(string $default = APP_NAMESPACE): static

- **Parameters**  
    - **$default** `string` The namespace used when the option is omitted.

Registers the `--namespace` / `-n` option.

#### addSuffixOption(): static

Registers the `--suffix` / `-s` flag.

#### addForceOption(): static

Registers the `--force` / `-f` flag.
