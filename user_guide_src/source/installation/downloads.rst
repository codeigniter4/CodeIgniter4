#######################
Downloading CodeIgniter
#######################

The CodeIgniter 4 open source project has its own 
`Github organization <https://github.com/codeigniter4>`_.

There are a number of repositories there of interest:

- `CodeIgniter4 <https://github.com/codeigniter4/CodeIgniter4>`_ 
    holds the codebase for the project, including unit testing
    and the source from which the user guide is built.
    This would be used by contributors to the project.
- `framework <https://github.com/codeigniter4/framework>`_ 
    holds the released versions of the framework.
    This would be normally be used by developers.
- `appstarter <https://github.com/codeigniter4/appstarter>`_ 
    holds the released application starter, with application
    and public folders, but with only a composer
    dependency on the framework.
    This is meant as the easy way to start a CodeIgniter 4 project.
- `userguide <https://github.com/codeigniter4/userguide>`_ 
    holds the pre-built user guide. It can be downloaded
    on its own, or `viewed online <https://codeigniter4.github.io/userguide>`_.
- `translations <https://github.com/codeigniter4/translations>`_ 
    holds translations of the CodeIgniter 4 system messages.
    Developers can use this for :doc:`localization </outgoing/localization>`.
- `coding-standard <https://github.com/codeigniter4/coding-standard>`_ 
    holds the coding conventions we use for source code that is
    part of the framework itself. 
    It is a dependency of the codebase repository, for contributors.

In all the above, the latest version of a repository can be downloaded
by selecting the "releases" link in the secondary navbar inside
the "Code" tab. The current (in development) version of each can
be cloned or downloaded by selecting the "Clone or download" dropdown
button on the right-hand side if the repository homepage.

Composer Packages
=================

We also maintain composer-installable packages on `packagist.org <https://packagist.org/search/?query=codeigniter4>`_.
These correspond to the repositories mentioned above:

- `codeigniter4/framework <https://packagist.org/packages/codeigniter4/framework>`_
- `codeigniter4/appstarter <https://packagist.org/packages/codeigniter4/appstarter>`_
- `codeigniter4/userguide <https://packagist.org/packages/codeigniter4/userguide>`_
- `codeigniter4/translations <https://packagist.org/packages/codeigniter4/translations>`_
- `codeigniter4/CodeIgniter4 <https://packagist.org/packages/codeigniter4/CodeIgniter4>`_
- `codeigniter4/coding-standard <https://packagist.org/packages/codeigniter4/codeigniter4-standard>`_

See the :doc:`Installation </installation/index>` page for more information.
