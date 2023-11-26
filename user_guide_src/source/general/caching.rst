################
Web Page Caching
################

CodeIgniter lets you cache your pages in order to achieve maximum
performance.

Although CodeIgniter is quite fast, the amount of dynamic information
you display in your pages will correlate directly to the server
resources, memory, and processing cycles utilized, which affect your
page load speeds. By caching your pages, since they are saved in their
fully rendered state, you can achieve performance much closer to that of
static web pages.

.. contents::
    :local:
    :depth: 2

How Does Caching Work?
======================

Caching can be enabled on a per-page basis, and you can set the length
of time that a page should remain cached before being refreshed. When a
page is loaded for the first time, the page will be cached using the
currently configured cache engine. On subsequent page loads, the cache
will be retrieved and sent to the requesting user's browser. If it has
expired, it will be deleted and refreshed before being sent to the
browser.

.. note:: The Benchmark tag is not cached so you can still view your page
    load speed when caching is enabled.

Configuring Caching
===================

Setting Cache Engine
--------------------

Before using Web Page Caching, you must set the cache engine up by editing
**app/Config/Cache.php**. See :ref:`libraries-caching-configuring-the-cache`
for details.

Setting $cacheQueryString
-------------------------

You can set whether or not to include the query string when generating the cache
with ``Config\Cache::$cacheQueryString``.

Valid options are:

- ``false``: (default) Disabled. The query string is not taken into account; the
  same cache is returned for requests with the same URI path but different query
  strings.
- ``true``: Enabled, take all query parameters into account. Be aware that this
  may result in numerous cache generated for the same page over and over
  again.
- **array**: Enabled, but only take into account the specified list of query
  parameters. E.g., ``['q', 'page']``.

Enabling Caching
================

To enable caching, put the following tag in any of your controller
methods:

.. literalinclude:: caching/001.php

Where ``$n`` is the number of **seconds** you wish the page to remain
cached between refreshes.

The above tag can go anywhere within a method. It is not affected by
the order that it appears, so place it wherever it seems most logical to
you. Once the tag is in place, your pages will begin being cached.

.. important:: If you change configuration options that might affect
    your output, you have to manually delete your cache.

Deleting Caches
===============

If you no longer wish to cache a page you can remove the caching tag and
it will no longer be refreshed when it expires.

.. note:: Removing the tag will not delete the cache immediately. It will
    have to expire normally.
