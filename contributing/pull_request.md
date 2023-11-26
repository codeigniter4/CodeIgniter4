# Sending a Pull Request

## Contributions

We expect all contributions to conform to our
[style guide](https://github.com/codeigniter4/CodeIgniter4/blob/develop/contributing/styleguide.md),
be commented (inside the PHP source files), be documented (in the
[user guide](https://codeigniter4.github.io/userguide/)), and unit tested (in
the [test folder](https://github.com/codeigniter4/CodeIgniter4/tree/develop/tests)).

Note, we expect all code changes or bug-fixes to be accompanied by one or more tests added to our test suite
to prove the code works. If pull requests are not accompanied by relevant tests, they will likely be closed.
Since we are a team of volunteers, we don't have any more time to work on the framework than you do. Please
make it as painless for your contributions to be included as possible. If you need help with getting tests
running on your local machines, ask for help on the forums. We would be happy to help out.

The [Open Source Guide](https://opensource.guide/) is a good first read for those new to contributing to open source!

## CodeIgniter Internals Overview

[CodeIgniter Internals Overview](./internals.md) should help contributors
understand how the core of the framework works. Specifically, it details the
information needed to create new packages for the core.

## Guidelines

Before we look into how to contribute to CodeIgniter4, here are some guidelines.
Your Pull Requests (PRs) need to meet our guidelines.

If your Pull Requests fail to pass these guidelines, they will be declined,
and you will need to re-submit when you've made the changes.
This might sound a bit tough, but it is required for us to maintain the quality of the codebase.

### PHP Style

- [CodeIgniter Coding Style Guide](./styleguide.md)

All code must conform to our [Style Guide](./styleguide.md), which is
based on PSR-12.

This makes certain that all submitted code is of the same format
as the existing code and ensures that the codebase will be as readable as possible.

You can fix most of the coding style violations by running this command in your terminal:

```console
composer cs-fix
```

You can check the coding style violations:

```console
composer cs
```

### Unit Testing

If you are not familiar with Unit Testing, see [the forum thread](https://forum.codeigniter.com/showthread.php?tid=81830).

Unit testing is expected for all CodeIgniter components. We use PHPUnit,
and run unit tests using GitHub Actions for each PR submitted or changed.

In the CodeIgniter project, there is a `tests` folder, with a structure
that parallels that of `system`.

The normal practice would be to have a unit test class for each of the
classes in `system`, named appropriately. For instance, the `BananaTest`
class would test the `Banana` class. There will be occasions when it is
more convenient to have separate classes to test different functionality
of a single CodeIgniter component.

See [Running System Tests](../tests/README.md)
and the [PHPUnit website](https://phpunit.de/) for more information.

### Comments

#### PHPDoc Comments

Source code should be commented using PHPDoc comment blocks. This means
implementation comments to explain potentially confusing sections of
code, and documentation comments before each public or protected
class/interface/trait, method and variable.

Do not add PHPDoc comments that are superficial, duplicated, or stating the obvious.

See the [phpDocumentor website](https://phpdoc.org/) for more
information.

#### Code Comments

Do not add comments that are superficial, duplicated, or stating the obvious.

### Documentation

The User Guide is an essential component of the CodeIgniter framework.

Each framework component or group of components needs a corresponding
section in the User Guide. Some of the more fundamental components will
show up in more than one place.

If you change anything that requires a change to documentation,
then you will need to add to the documentation.
New classes, methods, parameters, changing default values, changing behavior etc.
are all changes that require a change to documentation.

Also, the [Changelog](https://codeigniter4.github.io/CodeIgniter4/changelogs/index.html) must be updated for every change,
and [PHPDoc](https://github.com/codeigniter4/CodeIgniter4/blob/develop/phpdoc.dist.xml) blocks must be maintained.

See [Writing CodeIgniter Documentation](./documentation.rst).

#### Changelog

The [Changelog](https://codeigniter4.github.io/CodeIgniter4/changelogs/index.html), in the user guide, needs to be kept up-to-date. Not
all changes will need an entry in it, but the following items should.

- all breaking changes (BCs)
- all enhancements (new features, new classes, new APIs)
- other behavior changes
- deprecations
- major bug fixes

#### Upgrading Guide

If your PR requires users to do something when they upgrade CodeIgniter, or
changes the Config values,
the [Upgrading Guide](https://codeigniter4.github.io/CodeIgniter4/installation/upgrading.html)
is also needed.

- Add an instruction what to do when upgrading.
- If you add new properties or changes default values in Config files, add the
changes in *"Project Files > Content Changes > Config"*.

### CSS

See [Contribution CSS](./css.md).

### Compatibility

CodeIgniter4 requires [PHP 7.4](https://php.net/releases/7_4_0.php).

### Backwards Compatibility

Generally, we aim to maintain backwards compatibility between minor
versions of the framework. Any changes that break compatibility need a
good reason to do so, and need to be pointed out in the
[Upgrading](https://codeigniter4.github.io/userguide/installation/upgrading.html)
guide.

CodeIgniter4 itself represents a significant backwards compatibility
break with earlier versions of the framework.

#### Breaking Changes

In general, any change that would disrupt existing uses of the framework is considered a "Breaking Change" (BC) and will not be favorably considered. A few specific examples to pay attention to:

1. New classes/properties/constants in `system` are acceptable, but anything in the `app` directory that will be used in `system` should be backwards-compatible.
2. Any changes to non-private methods must be backwards-compatible with the original definition.
3. Deleting non-private properties or methods without prior deprecation notices is frowned upon and will likely be closed.
4. Deleting or renaming public classes and interfaces, as well as those not marked as `@internal`, without prior deprecation notices or not providing fallback solutions will also not be favorably considered.

### Mergeability

Your PRs need to be mergeable and GPG-signed before they will be
considered.

We suggest that you synchronize your repository's `develop` branch with
that in the main repository, and then your feature branch and your
develop branch, before submitting a PR. You will need to resolve any
merge conflicts introduced by changes incorporated since you started
working on your contribution.

### Branching

All bug fixes should be sent to the __"develop"__ branch, this is where the next bug fix version will be developed.

PRs with any enhancement should be sent to next minor version branch, e.g. __"4.3"__

The __"master"__ branch will always contain the latest stable version and is kept clean so a "hotfix" (e.g. an
emergency security patch) can be applied to the "master" branch to create a new version, without worrying
about other features holding it up. Any sent to the "master" branch will be closed automatically.

If you have multiple changes to submit,
please place all changes into their own branch on your fork.

**One thing at a time:** A pull request should only contain one change. That does not mean only one commit,
but one change - however many commits it took. The reason for this is that if you change X and Y,
but send a pull request for both at the same time, we might really want X but disagree with Y,
meaning we cannot merge the request. Using the Git-Flow branching model you can create new
branches for both of these features and send two requests.

A reminder: **please use separate branches for each of your PRs** - it will make it easier for you to keep
changes separate from each other and from whatever else you are doing with your repository!

### Signing

You must [GPG-sign](./signing.md) your work, certifying that you either wrote the work or
otherwise have the right to pass it on to an open-source project. See [Developer's Certificate of Origin](./DCO.md).

This is *not* just a "signed-off-by" commit, but instead, a digitally signed one.

See [Contribution signing](./signing.md) for details.

### Static Analysis on PHP code

We cannot, at all times, guarantee that all PHP code submitted on pull requests to be working well without
actually running the code. For this reason, we make use of two static analysis tools, [PHPStan][1]
and [Rector][2] to do the analysis for us.

These tools have already been integrated into our CI/CD workflow to minimize unannounced bugs. Pull requests
are expected that their code will pass these two. In your local machine, you can manually run these tools
so that you can fix whatever errors that pop up with your submission.

PHPStan is expected to scan the entire framework by running this command in your terminal:

```console
vendor/bin/phpstan analyse
```

Rector, on the other hand, can be run on the specific files you modified or added:

```console
vendor/bin/rector process --dry-run path/to/file
```

If you run it without `--dry-run`, Rector will fix the code:

```console
vendor/bin/rector process path/to/file
```

[1]: https://github.com/phpstan/phpstan-src
[2]: https://github.com/rectorphp/rector

## How-to Guide

The best way to contribute is to fork the CodeIgniter4 repository, and "clone" that to your development area. That sounds like some jargon, but "forking" on GitHub means "making a copy of that repo to your account" and "cloning" means "copying that code to your environment so you can work on it".

1. Set up Git ([Windows](https://git-scm.com/download/win), [Mac](https://git-scm.com/download/mac), & [Linux](https://git-scm.com/download/linux)).
2. Go to the [CodeIgniter4 repository](https://github.com/codeigniter4/CodeIgniter4).
3. [Fork](https://help.github.com/en/articles/fork-a-repo) it (to your GitHub account).
4. [Clone](https://help.github.com/en/articles/cloning-a-repository) your CodeIgniter repository: `git@github.com:<your-name>/CodeIgniter4.git`
   - `> git clone git@github.com:<your-name>/CodeIgniter4.git`
5. Install Composer dependencies.
   - `> cd CodeIgniter4/`
   - `> composer update`
6. Create a new [branch](https://help.github.com/en/articles/about-branches) in your project for each set of changes you want to make.
   - If your PR is for bug fixes:
      - `> git switch develop`
      - `> git switch -c <new-branch-name>`
   - If your PR has any enhancement, create new branch from next minor version branch, e.g. __"4.3"__:
      - `> git switch <next-minor-version-branch>`
      - `> git switch -c <new-branch-name>`
7. Fix existing bugs on the [Issue tracker](https://github.com/codeigniter4/CodeIgniter4/issues) after confirming that no one else is working on them.
8. [Commit](https://help.github.com/en/desktop/contributing-to-projects/committing-and-reviewing-changes-to-your-project) the changed files in your contribution branch.
   - `> git commit`
   - Commit messages are expected to be descriptive of why and what you changed specifically. Commit messages like "Fixes #1234" would be asked by the reviewer to be revised. [Atomic commit](https://en.wikipedia.org/wiki/Atomic_commit#Atomic_commit_convention) is recommended. See [Contribution Workflow](./workflow.md#commit-messages) for details.
9. If you have touched PHP code, run static analysis.
   - `> composer analyze`
10. Run unit tests on the specific file you modified. If there are no existing tests yet, please create one.
   - `> vendor/bin/phpunit tests/system/path/to/file/you/modified`
   - Make sure the tests pass to have a higher chance of merging.
11. [Push](https://docs.github.com/en/github/using-git/pushing-commits-to-a-remote-repository) your contribution branch to your fork.
   - `> git push origin <new-branch-name>`
12. Send a [pull request](https://docs.github.com/en/github/collaborating-with-issues-and-pull-requests/creating-a-pull-request-from-a-fork).
13. Label your pull request with the appropriate label if you can.

See [Contribution workflow](./workflow.md) for Git workflow details.

The codebase maintainers will now be alerted to the submission and someone from the team will respond. If your change fails to meet the guidelines, it will be rejected or feedback will be provided to help you improve it.

Once the maintainer handling your pull request is satisfied with it, they will approve the pull request and merge it into the "develop" branch. Your patch will now be part of the next release!

## Translating System Messages

If you wish to contribute to the system message translations,
then fork and clone the [translations repository](https://github.com/codeigniter4/translations)
separately from the codebase.

These are two independent repositories!
