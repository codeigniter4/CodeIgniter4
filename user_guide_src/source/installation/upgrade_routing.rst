Upgrade Routing
##################

.. contents::
    :local:
    :depth: 2

Documentations
==============

- `URI Routing Documentation CodeIgniter 3.x <http://codeigniter.com/userguide3/general/routing.html>`_
- :doc:`URI Routing Documentation CodeIgniter 4.x </incoming/routing>`

What has been changed
=====================

- In CI4 the Auto Routing is disabled by default.
- In CI4 the new more secure :ref:`auto-routing-improved` is introduced.
- In CI4 the routing is no longer configured by setting the routes as array.
- The Wildcard ``(:any)`` In CI3 will be the Placeholder ``(:segment)`` in CI4. The ``(:any)`` in CI4 matches multiple segements. See :ref:`URI Routing <routing-placeholder-any>`.

Upgrade Guide
=============

1. If you use the Auto Routing in the same way as CI3, you need to enable :ref:`auto-routing-legacy`.
2. You have to change the syntax of each routing line and append it in **app/Config/Routes.php**. For example:

    - ``$route['journals'] = 'blogs';`` to ``$routes->add('journals', 'Blogs::index');``. This would map to the ``index()`` method in the ``Blogs`` controller.
    - ``$route['product/(:any)'] = 'catalog/product_lookup';`` to ``$routes->add('product/(:segment)', 'Catalog::productLookup');``. Don't forget to replace ``(:any)`` with ``(:segment)``.
    - ``$route['login/(.+)'] = 'auth/login/$1';`` to ``$routes->add('login/(.+)', 'Auth::login/$1');``

    .. note:: For backward compatibility, ``$routes->add()`` is used here. But we
        strongly recommend to use :ref:`routing-http-verb-routes` like
        ``$routes->get()`` instead of ``$routes->add()`` for security.

Code Example
============

CodeIgniter Version 3.x
------------------------
Path: **application/config/routes.php**:

.. literalinclude:: upgrade_routing/ci3sample/001.php

CodeIgniter Version 4.x
-----------------------
Path: **app/Config/Routes.php**:

.. literalinclude:: upgrade_routing/001.php

.. note:: For backward compatibility, ``$routes->add()`` is used here. But we
    strongly recommend to use :ref:`routing-http-verb-routes` like
    ``$routes->get()`` instead of ``$routes->add()`` for security.
