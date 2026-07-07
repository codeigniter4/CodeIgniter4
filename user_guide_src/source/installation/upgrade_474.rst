#############################
Upgrading from 4.7.3 to 4.7.4
#############################

Please refer to the upgrade instructions corresponding to your installation method.

- :ref:`Composer Installation App Starter Upgrading <app-starter-upgrading>`
- :ref:`Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading <adding-codeigniter4-upgrading>`
- :ref:`Manual Installation Upgrading <installing-manual-upgrading>`

.. contents::
    :local:
    :depth: 2

**********************
Mandatory File Changes
**********************

****************
Breaking Changes
****************

HTTPS Detection Behind Proxies
==============================

For security reasons, ``IncomingRequest::isSecure()`` no longer trusts the
``X-Forwarded-Proto`` and ``Front-End-Https`` headers unless the request comes
from a trusted proxy. See the
`Security advisory GHSA-7wmf-pw8j-mc78 <https://github.com/codeigniter4/CodeIgniter4/security/advisories/GHSA-7wmf-pw8j-mc78>`_
for more information.

If your application runs behind a reverse proxy or load balancer that
terminates TLS, you must register the proxy in ``Config\App::$proxyIPs``, e.g.::

    public array $proxyIPs = [
        '10.0.1.200'     => 'X-Forwarded-For',
        '192.168.5.0/24' => 'X-Forwarded-For',
    ];

Otherwise, ``isSecure()``, ``force_https()``, and
``Config\App::$forceGlobalSecureRequests`` will treat such requests as
non-HTTPS, which may result in redirect loops.

.. note:: On dual-stack servers, a proxy's IPv4 address may be reported as an
    IPv4-mapped IPv6 address (e.g., ``::ffff:192.168.5.21``), which does not
    match an IPv4 entry such as ``192.168.5.0/24``. In that case, add the
    IPv4-mapped form to ``Config\App::$proxyIPs``, e.g.,
    ``::ffff:192.168.5.21`` or ``::ffff:192.168.5.0/120``.

*********************
Breaking Enhancements
*********************

*************
Project Files
*************

Some files in the **project space** (root, app, public, writable) received updates. Due to
these files being outside of the **system** scope they will not be changed without your intervention.

.. note:: There are some third-party CodeIgniter modules available to assist
    with merging changes to the project space:
    `Explore on Packagist <https://packagist.org/explore/?query=codeigniter4%20updates>`_.

Content Changes
===============

The following files received significant changes (including deprecations or visual adjustments)
and it is recommended that you merge the updated versions with your application:

Config
------

- @TODO

All Changes
===========

This is a list of all files in the **project space** that received changes;
many will be simple comments or formatting that have no effect on the runtime:

- @TODO
