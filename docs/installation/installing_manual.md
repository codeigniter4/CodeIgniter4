# Manual Installation

The [CodeIgniter 4 framework](https://github.com/codeigniter4/framework) repository holds the released versions of the framework. It is intended for developers who do not wish to use Composer.

Develop your app inside the **app** folder, and the **public** folder will be your public-facing document root. Do not change anything inside the **system** folder!

!!! note "Note"
    This is the installation technique closest to that described for [CodeIgniter 3](https://codeigniter.com/userguide3/installation/index.html).

## Installation

Download the [latest version](https://github.com/CodeIgniter4/framework/releases/latest), and extract it to become your project root.

!!! note "Note"
    Before v4.4.0, the CodeIgniter autoloader did not allow special characters that are illegal in filenames on certain operating systems. The symbols that can be used are `/`, `_`, `.`, `:`, `\` and space. So if you installed CodeIgniter under a folder that contains special characters like `(`, `)`, etc., CodeIgniter didn't work. Since v4.4.0, this restriction has been removed.

## Initial Configuration

After installation, a few initial configurations are required. See [Initial Configuration](running.md#initial-configuration) for details.

## Upgrading

Download a new copy of the framework, and then replace the **system** folder.

Read the [upgrade instructions](upgrading.md) and [change log](../changelogs/index.md), and check Breaking Changes and Enhancements.

## Pros

Download and run.

## Cons

You need to check for file changes in the **project space** (root, app, public, tests, writable) and merge them by yourself.

## Structure

Folders in your project after setup:

- app, public, tests, writable, system

## Translations Installation

If you want to take advantage of the system message translations, they can be added to your project in a similar fashion.

Download the [latest version of them](https://github.com/codeigniter4/translations/releases/latest). Extract the downloaded zip, and copy the contents of its **Language** folder to your **app/Language** folder.

This would need to be repeated to incorporate any updates to the translations.
