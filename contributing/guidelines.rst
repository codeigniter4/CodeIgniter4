=======================
Contribution Guidelines
=======================

Your Pull Requests (PRs) need to meet our guidelines. If a PR fails
to pass these guidelines, it will be declined and you will need to re-submit
when you’ve made the changes. This might sound a bit tough, but it is required
for us to maintain quality of the code-base.

PHP Style
=========

All code must conform to our `Style Guide
<./styleguide.rst>`_, which is
essentially the `Allman indent style
<https://en.wikipedia.org/wiki/Indent_style#Allman_style>`_, with
elaboration on naming and readable operators.

This makes certain that all code is the same format as the
existing code and means it will be as readable as possible.

Our Style Guide is similar to PSR-1 and PSR-2, from PHP-FIG,
but not necessarily the same or compatible.

Unit Testing
============

Unit testing is expected for all CodeIgniter components.
We use PHPunit, and run unit tests using travis-ci
for each PR submitted or changed.

In the CodeIgniter project, there is a ``tests`` folder, with a structure that
parallels that of ``system``.

The normal practice would be to have a unit test class for each of the classes
in ``system``, named appropriately. For instance, the ``BananaTest``
class would test the ``Banana`` class. There will be occasions when
it is more convenient to have separate classes to test different functionality
of a single CodeIgniter component.

See the `PHPUnit website <https://phpunit.de/>`_ for more information.

PHPdoc Comments
===============

Source code should be commented using PHPdoc comments blocks.
Thie means implementation comments to explain potentially confusing sections
of code, and documentation comments before each public or protected
class/interface/trait, method and variable.

See the `phpDocumentor website <https://phpdoc.org/>`_ for more information.

Documentation
=============

The User Guide is an essential component of the CodeIgniter framework.

Each framework component or group of components needs a corresponding
section in the User Guide. Some of the more fundamental components will
show up in more than one place.

Change Log
==========

The change-log, in the user guide root, needs to be kept up-to-date.
Not all changes will need an entry in it, but new classes, major or BC changes
to existing classes should. Once we have a stable release, bug fixes would
appear in the changelog too.

The changelog is independently maintained by the framework release manager
Make sure that your PR descriptions help us decide if the contribution should
be highlighted in the next release after it has been merged.

PHP Compatibility
=================

CodeIgniter4 requires PHP 7.2.

Backwards Compatibility
=======================

Generally, we aim to maintain backwards compatibility between minor
versions of the framework. Any changes that break compatibility need
a good reason to do so, and need to be pointed out in the
`Upgrading <https://codeigniter4.github.io/userguide/installation/upgrading.html>`_ guide.

CodeIgniter4 itself represents a significant backwards compatibility break
with earlier versions of the framework.

Mergeability
============

Your PRs need to be mergeable and GPG-signed before they will be considered.

We suggest that you synchronize your repository's ``develop`` branch with
that in the main repository, and then your feature branch and
your develop branch, before submitting a PR.
You will need to resolve any merge conflicts introduced by changes
incorporated since you started working on your contribution.
