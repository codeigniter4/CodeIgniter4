CodeIgniter Repositories
########################

.. contents::
    :local:
    :depth: 2

codeigniter4 organization
=========================

The CodeIgniter 4 open source project has its own
`GitHub organization <https://github.com/codeigniter4>`_.

There are several development repositories, of interest to potential contributors:

+------------------+--------------+-----------------------------------------------------------------+
| Repository       | Audience     | Description                                                     |
+==================+==============+=================================================================+
| CodeIgniter4_    | contributors | Project codebase, including tests & user guide sources          |
+------------------+--------------+-----------------------------------------------------------------+
| translations_    | developers   | System message translations                                     |
+------------------+--------------+-----------------------------------------------------------------+
| coding-standard_ | contributors | Coding style conventions & rules                                |
+------------------+--------------+-----------------------------------------------------------------+
| devkit_          | developers   | Development toolkit for CodeIgniter libraries and projects      |
+------------------+--------------+-----------------------------------------------------------------+
| settings_        | developers   | Settings Library for CodeIgniter 4                              |
+------------------+--------------+-----------------------------------------------------------------+
| shield_          | developers   | Authentication and Authorization Library for CodeIgniter 4      |
+------------------+--------------+-----------------------------------------------------------------+
| tasks_           | developers   | Task Scheduler for CodeIgniter 4                                |
+------------------+--------------+-----------------------------------------------------------------+
| cache_           | developers   | PSR-6 and PSR-16 Cache Adapters for CodeIgniter 4               |
+------------------+--------------+-----------------------------------------------------------------+

.. _CodeIgniter4: https://github.com/codeigniter4/CodeIgniter4
.. _translations: https://github.com/codeigniter4/translations
.. _coding-standard: https://github.com/CodeIgniter/coding-standard
.. _devkit: https://github.com/codeigniter4/devkit
.. _settings: https://github.com/codeigniter4/settings
.. _shield: https://codeigniter4.github.io/shield
.. _tasks: https://github.com/codeigniter4/tasks
.. _cache: https://github.com/codeigniter4/cache

There are also several deployment repositories, referenced in the installation directions.
The deployment repositories are built automatically when a new version is released, and they
are not directly contributed to.

+------------------+--------------+-----------------------------------------------------------------+
| Repository       | Audience     | Description                                                     |
+==================+==============+=================================================================+
| framework_       | developers   | Released versions of the framework                              |
+------------------+--------------+-----------------------------------------------------------------+
| appstarter_      | developers   | Starter project (app/public/writable).                          |
|                  |              | Dependent on "framework"                                        |
+------------------+--------------+-----------------------------------------------------------------+
| userguide_       | anyone       | Pre-built user guide                                            |
+------------------+--------------+-----------------------------------------------------------------+

.. _framework: https://github.com/codeigniter4/framework
.. _appstarter: https://github.com/codeigniter4/appstarter
.. _userguide: https://github.com/codeigniter4/userguide

In all the above, the latest version of a repository can be downloaded
by selecting the "releases" link in the secondary navbar inside
the "Code" tab of its GitHub repository page. The current (in development) version of each can
be cloned or downloaded by selecting the "Clone or download" dropdown
button on the right-hand side if the repository homepage.

Composer Packages
=================

We also maintain composer-installable packages on `packagist.org <https://packagist.org/search/?query=codeigniter4>`_.
These correspond to the repositories mentioned above:

- `codeigniter4/framework <https://packagist.org/packages/codeigniter4/framework>`_
- `codeigniter4/appstarter <https://packagist.org/packages/codeigniter4/appstarter>`_
- `codeigniter4/translations <https://packagist.org/packages/codeigniter4/translations>`_
- `codeigniter/coding-standard  <https://packagist.org/packages/codeigniter/coding-standard>`_
- `codeigniter4/devkit <https://packagist.org/packages/codeigniter4/devkit>`_
- `codeigniter4/settings <https://packagist.org/packages/codeigniter4/settings>`_
- `codeigniter4/shield <https://packagist.org/packages/codeigniter4/shield>`_
- `codeigniter4/cache <https://packagist.org/packages/codeigniter4/cache>`_

See the :doc:`Installation </installation/index>` page for more information.

CodeIgniter 4 Projects
======================

We maintain a `codeigniter4projects <https://github.com/codeigniter4projects>`_ organization
on GitHub as well, with projects that are not part of the framework,
but which showcase it or make it easier to work with!

+------------------+--------------+-----------------------------------------------------------------+
| Repository       | Audience     | Description                                                     |
+==================+==============+=================================================================+
| website_         | developers   | The codeigniter.com website, written in CodeIgniter 4           |
+------------------+--------------+-----------------------------------------------------------------+
| playground_      | developers   | Basic code examples in project form. Still growing.             |
+------------------+--------------+-----------------------------------------------------------------+

.. _website: https://github.com/codeigniter4projects/website
.. _playground: https://github.com/codeigniter4projects/playground

These are not composer-installable repositories.
