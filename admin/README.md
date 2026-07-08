# CodeIgniter 4 Admin

This folder contains tools or docs useful for project maintainers.

## Repositories inside https://github.com/codeigniter4

-   **CodeIgniter4** is the main development repository.  
    It supports issues and pull requests, and has a rule to enforce GPG-signed commits.  
    In addition to the framework source, it includes unit testing and documentation source.  
    The three repositories following are built from this one as part of the release workflow.  
    This repo is meant to be forked by contributors.
-   **framework** is the released developer repository.  
    It contains all the main pieces of the framework that developers would use to
    build their apps, but not the framework unit testing or the user guide source.  
    It is meant to be downloaded by developers, or composer-installed.  
    This is a read-only repository.
-   **appstarter** is the released application starter repository.  
    It is derived from the framework's `app` and `public` folders, with
    a composer requirement dependency to pull in the framework itself.  
    It is meant to be downloaded or composer-installed.  
    This is a read-only repository.
-   **userguide** is released documentation publishing repository.  
    It contains built versions of the user guide, corresponding to the
    framework releases.  
    It could be downloaded, forked or potentially composer-installed.  
    This is a read-only repository.
-   **coding-standard** <https://github.com/CodeIgniter/coding-standard> is the coding style standards repository.  
    It contains PHP-CS-Fixer rules to ensure consistent code style
    within the framework itself.  
    It is meant to be composer-installed.
-   **translations** is the repository holding official translations of
    the locale-dependent system messages.  
    It is community-maintained, and accepts issues and pull requests.  
    It could be downloaded, forked or composer-installed.

## Contributor Scripts

-   **setup.sh** installs a git pre-commit hook into a contributor's
    local clone of their fork of the `CodeIgniter4` repository.
-   **pre-commit** runs PHP Lint and PHP CodeSniffer on any files
    to be added as part of a git commit, ensuring that they conform to the
    framework coding style standards, and automatically fixing what can be.

## Release Scripts

The release process is detailed in [RELEASE.md](./RELEASE.md). These scripts
are used as part of that process:

-   **generate-changelog.php** prepends the CHANGELOG.md entry for a new
    release, built from GitHub's auto-generated release notes and the
    SECURITY section of the version's detailed changelog.  
    Usage: `php admin/generate-changelog.php 4.x.x [--dry-run]`
-   **prepare-release.php** creates the `release-4.x.x` branch and updates
    version references in the framework source, the user guide, and the
    distribution build script.  
    Usage: `php admin/prepare-release.php 4.x.x`
-   **create-new-changelog.php** creates the changelog and upgrade guide
    stubs for the next version and adds them to their index files.  
    Usage: `php admin/create-new-changelog.php <current_version> <new_version>`

## Other Stuff

-   The **framework** and **starter** subfolders contain files that will over-ride
    those from the development repository, when the distribution repositories
    are built.
-   The subfolders inside `admin` contain "next release" files in the case of 
    `codeigniter4` and over-written distribution files in the other cases.
-   The CHANGELOG.md file is generated from GitHub's auto-generated release
    notes, as described in [RELEASE.md](./RELEASE.md).
