CodeIgniter Repositories
########################

The CodeIgniter 4 open source project has its own
`Github organization <https://github.com/codeigniter4>`_.

There are several development repositories, of interest to potential contributors:

+------------------+--------------+-----------------------------------------------------------------+
+ Repository       + Audience     + Description                                                     +
+==================+==============+=================================================================+
+ CodeIgniter4     + contributors + Project codebase, including tests & user guide sources          +
+------------------+--------------+-----------------------------------------------------------------+
+ devstarter       + developers   + Starter project (app/public/writable).                          +
+                  +              + Dependent on develop branch of codebase repository              +
+------------------+--------------+-----------------------------------------------------------------+
+ translations     + developers   + System message translations                                     +
+------------------+--------------+-----------------------------------------------------------------+
+ coding-standard  + contributors + Coding style conventions & rules                                +
+------------------+--------------+-----------------------------------------------------------------+
+                  +              +                                                                 +
+------------------+--------------+-----------------------------------------------------------------+

There are also several deployment repositories, referenced in the installation directions.
The deployment repositories are built automatically when a new version is released, and they
are not directly contributed to.

+------------------+--------------+-----------------------------------------------------------------+
+ Repository       + Audience     + Description                                                     +
+==================+==============+=================================================================+
+ framework        + developers   + Released versions of the framework                              +
+------------------+--------------+-----------------------------------------------------------------+
+ appstarter       + developers   + Starter project (app/public/writable).                          +
+                  +              + Dependent on "framework"                                        +
+------------------+--------------+-----------------------------------------------------------------+
+ userguide        + anyone       + Pre-built user guide                                            +
+------------------+--------------+-----------------------------------------------------------------+
+                  +              +                                                                 +
+------------------+--------------+-----------------------------------------------------------------+

In all the above, the latest version of a repository can be downloaded
by selecting the "releases" link in the secondary navbar inside
the "Code" tab of its Github repository page. The current (in development) version of each can
be cloned or downloaded by selecting the "Clone or download" dropdown
button on the right-hand side if the repository homepage.

Composer Packages
=================

We also maintain composer-installable packages on `packagist.org <https://packagist.org/search/?query=codeigniter4>`_.
These correspond to the repositories mentioned above:

- `codeigniter4/framework <https://packagist.org/packages/codeigniter4/framework>`_
- `codeigniter4/appstarter <https://packagist.org/packages/codeigniter4/appstarter>`_
- `codeigniter4/devstarter <https://packagist.org/packages/codeigniter4/devstarter>`_
- `codeigniter4/userguide <https://packagist.org/packages/codeigniter4/userguide>`_
- `codeigniter4/translations <https://packagist.org/packages/codeigniter4/translations>`_
- `codeigniter4/CodeIgniter4 <https://packagist.org/packages/codeigniter4/CodeIgniter4>`_
- `codeigniter4/coding-standard <https://packagist.org/packages/codeigniter4/codeigniter4-standard>`_

See the :doc:`Installation </installation/index>` page for more information.

CodeIgniter 4 Projects
======================

We maintain a `codeigniter4projects <https://github.com/codeigniter4projects>`_ organization
on Github as well, with projects that are not part of the framework, 
but which showcase it or make it easier to work with!

+------------------+--------------+-----------------------------------------------------------------+
+ Repository       + Audience     + Description                                                     +
+==================+==============+=================================================================+
+ website2         + developers   + The codeigniter.com website, written in CodeIgniter 4           +
+------------------+--------------+-----------------------------------------------------------------+
+ module-tests     + plugin       + PHPunit testing scaffold for CI4 module / plugin developers     +
+                  + developers   +                                                                 +
+------------------+--------------+-----------------------------------------------------------------+
+ project-tests    + app          + PHPunit testugn scaffold for CI4 projects                       +
+                  + developers   +                                                                 +
+------------------+--------------+-----------------------------------------------------------------+
+                  +              +                                                                 +
+------------------+--------------+-----------------------------------------------------------------+

These are not composer-installable repositories.
