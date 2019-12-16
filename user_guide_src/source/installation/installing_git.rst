Git Installation
###############################################################################

This would *not* be suitable for app development, but *is* suitable
for contributing to the framework.

Installation
-------------------------------------------------------

Install the latest version of the codebase by

- forking the `codebase  <https://github.com/codeigniter4/CodeIgniter4>`_ to your github account
- cloning **your** forked repository locally

Setting Up
-------------------------------------------------------

The command above will create a "CodeIgniter4" folder.
Feel free to rename that as you see fit.

You will want to set up a remote repository alias, so you can synchronize
your repository with the main one::

    git remote add upstream https://github.com/codeigniter4/CodeIgniter4.git

Copy the provided ``env`` file to ``.env``, and use that for your git-ignored configuration settings,

Copy the provided ``phpunit.xml.dist`` to ``phpunit.xml`` and tailor it as needed,
if you want custom unit testing for the framework.

Upgrading
-------------------------------------------------------

Update your code anytime::

    git checkout develop
    git pull upstream develop
    git push origin develop

Merge conflicts may arise when you pull from "upstream". 
You will need to resolve them locally.

Pros
-------------------------------------------------------

- You have the latest version of the codebase (unreleased)
- You can propose contributions to the framework, by creating a 
    feature branch and submitting a pull request for it to the main repo
- a pre-commit hook is installed for your repo, that binds it to the
    coding-standard we use

Cons
-------------------------------------------------------

You need to resolve merge conflicts when you synch with the repo.

You would not use this technique for app development.

Structure
-------------------------------------------------------

Folders in your project after set up:

- app, public, system, tests, user_guide_src, writable


Translations Installation
============================================================

If you wish to contribute to the system message translations,
then fork and clone the `translations repository
<https://github.com/codeigniter4/translations>`_ separately from the codebase. 
These are two independent repositories!


Coding Standards Installation
============================================================

This is bound and installed automatically as part of the
codebase installation.

If you wish to use it inside your project too,
``composer require codeigniter4/translations @beta``