#CodeIgniter 4 Admin

This folder contains tools or docs useful for project maintainers.

##Repositories Organization, inside https://github.com/codeigniter4

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
    It could be downloaded, forked or composer-installed.  
    This is a read-only repository.

-   **coding-standard** is the coding style standards repository.  
    It contains PHP CodeSniffer rules to ensure consistent code style
    within the framework itself.  
    It is meant to be composer-installed.
-   **translations** is the repository holding official translations of
    the locale-dependent system messages.  
    It is community-maintained, and accepts issues and pull requests.  
    It could be downloaded, forked or composer-installed.

##Contributor Scripts

-   **setup.sh** installs a git pre-commit hook into a contributor's
    local clone of their fork of the `CodeIgniter4` repository.
-   **pre-commit** runs PHP Lint and PHP CodeSniffer on any files
    to be added as part of a git commit, ensuring that they conform to the
    framework coding style standards, and automatically fixing what can be.

##Maintainer Scripts

-   **docbot** re-builds the user guide from the RST source for it,
    and optionally deploys it to the `gh-pages` branch of the main
    repository (if the user running it has maintainer rights on that repo).  
    See the [writeup](./docbot.md).

##Release Building Scripts

The release workflow is detailed in its own writeup; these are the main
scripts used by the release manager:

-   **pre-release** builds a new release branch in the main repo, for vetting.  
    This includes updating version dependencies or constants,
    generating version(s) of the user guide; and possibly
    moving or ignoring stuff, distinguishing release from development.
    If successful, it will leave a `releasing` file, with the version number
    in it.
-   **release** builds release branches for the derived repositories
    (framework, appstarter and userguide).  
    These are pushed to the respective repositories (if the user has maintainer
    rights), but the actual associated releases are created on github.com manually, so 
    that additional binaries can be added to the release if appropriate.
-   **post-release** cleans up after a release, eg. setting up the changelog for
    the next release.


##Other Stuff

-   **release-notes.bb** is a boilerplate for forum announcements of a new release.  
    It is marked up using [BBcode](https://en.wikipedia.org/wiki/BBCode).