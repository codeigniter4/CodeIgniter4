#############################
Upgrading from 4.1.6 to 4.1.7
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

- ``get_cookie()`` when ``$xssClean`` is true changed the output. Now it uses ``FILTER_SANITIZE_FULL_SPECIAL_CHARS``, not ``FILTER_SANITIZE_STRING``. Make sure the change is acceptable or not. Note that using XSS filtering is a bad practice. It does not prevent XSS attacks perfectly. Using ``esc()`` with the correct ``$context`` in the views is recommended.
