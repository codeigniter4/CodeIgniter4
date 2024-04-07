####################################
Cross-Origin Resource Sharing (CORS)
####################################

.. versionadded:: 4.5.0

Cross-Origin Resource Sharing (CORS) is an HTTP-header based security mechanism
that allows a server to indicate any origins (domain, scheme, or port) other than
its own from which a browser should permit loading resources.

CORS works by adding headers to HTTP requests and responses to indicate whether
the requested resource can be shared across different origins, helping to prevent
malicious attacks like cross-site request forgery (CSRF) and data theft.

If you are not familiar with CORS and CORS headers, please read the
`MDN documentation on CORS`_.

.. _MDN documentation on CORS: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS#the_http_response_headers

CodeIgniter provides the CORS filter and helper class.

.. contents::
    :local:
    :depth: 2

****************
Configuring CORS
****************

Setting Default Config
======================

CORS can be configured by **app/Config/Cors.php**.

At a minimum, the following items in the ``$default`` property must be set:

- ``allowedOrigins``: List explicitly the Origin(s) you want to allow.
- ``allowedHeaders``: List explicitly the HTTP headers you want to allow.
- ``allowedMethods``: List explicitly the HTTP methods you want to allow.

.. warning:: Based on the principle of least privilege, only the minimum necessary
    Origin, Methods, and Headers should be allowed.

If you send credentials (e.g., cookies) with a cross-origin request, set
``supportsCredentials`` to ``true``.

Enabling CORS
=============

To enable CORS, you need to do two things:

1. Specify the ``cors`` filter to routes that you permit CORS.
2. Add **OPTIONS** routes for CORS Preflight Requests.

Set against Routes
------------------

You can set the ``cors`` filter to routes with **app/Config/Routes.php**.

E.g.,

.. literalinclude:: cors/001.php

Don't forget to add OPTIONS routes for Preflight Requests. Because Controller
Filters (except for Required Filters) do not work if the route does not exist.

The CORS filter handles all Preflight Requests, so the closure controllers
for the OPTIONS routes are not normally called.

Set in Config\\Filters
----------------------

Alternatively, you can set the ``cors`` filter to URI paths in **app/Config/Filters.php**.

E.g.,

.. literalinclude:: cors/002.php

Don't forget to add OPTIONS routes for Preflight Requests. Because Controller
Filters (except for Required Filters) do not work if the route does not exist.

E.g.,

.. literalinclude:: cors/003.php

The CORS filter handles all Preflight Requests, so the closure controller
for the OPTIONS routes is not normally called.

Checking Routes and Filters
===========================

After configuration, you can check the routes and filters with the :ref:`routing-spark-routes`
command.

Setting Another Config
======================

If you want to use a different configuration than the default configuration, add
a property to **app/Config/Cors.php**.

For example, add the ``$api`` property.

.. literalinclude:: cors/004.php

The property name (``api`` in the above example) will become the configuration name.

Then, specify the property name as the filter argument like ``cors:api``:

.. literalinclude:: cors/005.php

You can also use :ref:`filters-filters-filter-arguments`.

***************
Class Reference
***************

.. php:namespace:: CodeIgniter\HTTP

.. php:class:: Cors

.. php:method:: addResponseHeaders(RequestInterface $request, ResponseInterface $response): ResponseInterface

    :param RequestInterface $request: Request instance
    :param ResponseInterface $response: Response instance
    :returns: Response instance
    :rtype: ResponseInterface

    Adds response headers for CORS.

.. php:method:: handlePreflightRequest(RequestInterface $request, ResponseInterface $response): ResponseInterface

    :param RequestInterface $request: Request instance
    :param ResponseInterface $response: Response instance
    :returns: Response instance
    :rtype: ResponseInterface

    Handles Preflight Requests.

.. php:method:: isPreflightRequest(IncomingRequest $request): bool

    :param IncomingRequest $request: Request instance
    :returns: True if it is a Preflight Request.
    :rtype: bool

    Checks if the request is a Preflight Request.
