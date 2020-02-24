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
    It is derived from the framework's `application` and `public` folders, with
    a composer requirement dependency to pull in the framework itself.  
    It is meant to be downloaded or composer-installed.  
    This is a read-only repository.
-   **userguide** is released documentation publishing repository.  
    It contains built versions of the user guide, corresponding to the
    framework releases.  
    It could be downloaded, forked or potentially composer-installed.  
    This is a read-only repository.

-   **coding-standard** is the coding style standards repository.  
    It contains PHP CodeSniffer rules to ensure consistent code style
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

## Maintainer Scripts

-   **release-config** holds variables used for the maintainer & release building
-   **docbot** re-builds the user guide from the RST source for it,
    and optionally deploys it to the `gh-pages` branch of the main
    repository (if the user running it has maintainer rights on that repo).  
    See the [writeup](./docbot.md).

## Release Building Scripts

The release workflow is detailed in its own writeup; these are the main
scripts used by the release manager:

-   **release** builds a new release branch in the main repo, for vetting.  
    This includes updating version dependencies or constants,
    generating version(s) of the user guide; and possibly
    moving or ignoring stuff, distinguishing release from development.
    If successful, it will update the `config` file, with the version number
    in it, and it will run the related scripts following, to revise
    the release distributions.  
	Usage: `admin/release version qualifier`
-   **release-framework** builds the distributable framework repo.  
    It could be used on its own, but is normally part of `release`.
-   **release-appstarter** builds the distributable appstarter repo.  
    It could be used on its own, but is normally part of `release`.
-   **release-userguide** builds the distributable userguide repo.  
    It could be used on its own, but is normally part of `release`.
-   **release-deploy** pushes the release changes to the appropriate github
    repositories. Tag & create releases on github. This is not easily reversible!  
	Usage: `admin/release-deploy version qualifier`
-   **release-revert** can be used to restore your repositories to the state they
    were in before you started a release. **IF** you haven't deployed. 
    This is in case you decide not to proceed with the release, for any reason. 
    Remember to be polite when running it.


## Other Stuff

-   **release-notes.bb** is a boilerplate for forum announcements of a new release.  
    It is marked up using [BBcode](https://en.wikipedia.org/wiki/BBCode).
-   The **framework** and **starter** subfolders contain files that will over-ride
    those from the development repository, when the distribution repositories
    are built.
-   The subfolders inside `admin` contain "next release" files in the case of 
    `codeigniter4` and over-written distribution files in the other cases.
-   The CHANGELOG.md file is auto-generated using the [GitHub Changelog Generator](https://github.com/github-changelog-generator/github-changelog-generator)
