############
Installation
############

CodeIgniter4 can be installed in a number of different ways: manually, 
using `Composer <https://getcomposer.org>`_, or even using 
`Git <https://git-scm.com/>`_. This section addresses how to use
each technique, and explains some of the pros and cons of them.

Once installed, read the next section, :doc:`running your app </installation/running>`.

.. note:: Before using CodeIgniter 4, make sure that your server meets the
          :doc:`requirements </intro/requirements>`.

Manual Installation
============================================================

Install the framework by downloading the latest version,
and extract it to become your project root. Your document
root will be the ``public`` folder inside here.

Pros:

- download and run; this is the installation technique
  closest to that described for `CodeIgniter 3 <https://www.codeigniter.com/user_guide/installation/index.html>`_.

Cons:

- upgrading the framework is done by downloading a new copy
  of the framework, and then following the upgrade
  directions to merge that with your project (typically
  replace the ``system`` folder and inspect designated
  ``application/Config`` folders for affected changes).

Resulting folder structure:

- application
- public
- system
- writable

Composer Installation
============================================================

Install the latest version of the framework with::

    > composer create-project codeigniter4/framework PROJECT_ROOT

Pros:

- simple installation

Cons:

- upgrading the framework is done by::

        > composer update

  You would then need to resolve merge conflicts for any framework
  changes to ``application`` files that you modified for your app.

Resulting folder structure:

- application
- public
- system
- writable


Codebase Installation
============================================================

Install the latest version of the codebase by

- forking the codebase to your github account
- cloning your forked repository locally

Pros:

- you get the latest version of the codebase
- you can propose contributions by creating a feature branch, and then
  submitting a pull request to the main repository once you have
  pushed your feature branch to your repository
- a pre-commit hook is installed for this repo that binds it to the
  coding-standard repo, and then automatically runs
  PHP Code Sniffer and fixes any fixable issues whenever you commit.

Cons:

- you need to add a git remote alias to your project, so you can
  pull codebase changes::

        > git remote add upstream https://github.com/codeigniter4/CodeIgniter4.git

- upgrading the framework is done by::

        > git checkout develop
        > git pull upstream develop
        > git push origin develop

  You would then need to resolve merge conflicts for any framework
  changes that conflict with modifications you have made.

Resulting folder structure:

- application
- public
- system
- tests
- user_guide_src
- writable

App Starter Installation
============================================================

Install the latest version of the framework's app starter with::

    > composer create-project codeigniter4/appstarter PROJECT_ROOT

Pros:

- simple installation

Cons:

- upgrading the framework is done by::

        > composer update

  You would then need to resolve merge conflicts for any framework
  changes to ``application`` files that you modified for your app.

Resulting folder structure:

- application
- public
- writable
- vendor/codeigniter4/framework

    - application
    - public
    - system

Translations Installation
============================================================

Install the available localizations to an existing CodeIgniter 4
project with::

    > composer require codeigniter4/translations

Update the translations at any time with::

    > composer update

When the translations are installed, they are added to the appropriate namespace.
See the :doc:`localization page </outgoing/localization>`
for guidance.

Resulting folder structure:

...

- vendor

    - codeigniter4

        - translations

Coding Standards Installation
============================================================

This is bound and installed automatically as part of the 
codebase installation.
