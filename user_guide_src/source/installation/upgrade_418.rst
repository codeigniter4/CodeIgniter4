#############################
Upgrading from 4.1.7 to 4.1.8
#############################

Please refer to the upgrade instructions corresponding to your installation method.

- :ref:`Composer Installation App Starter Upgrading <app-starter-upgrading>`
- :ref:`Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading <adding-codeigniter4-upgrading>`
- :ref:`Manual Installation Upgrading <installing-manual-upgrading>`

.. contents::
    :local:
    :depth: 2

Breaking Changes
****************

-  Due to a security issue in the ``API\ResponseTrait`` all trait methods are now scoped to ``protected``. See the `Security advisory GHSA-7528-7jg5-6g62 <https://github.com/codeigniter4/CodeIgniter4/security/advisories/GHSA-7528-7jg5-6g62>`_ for more information.
