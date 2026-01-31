.. _ci47-rest-part2:

Auto Routing & Your First Endpoint
##################################

.. contents::
    :local:
    :depth: 2

In this section, we will enable CodeIgniter's *Improved Auto Routing* feature and create a simple JSON endpoint to confirm everything is wired up correctly.

Why Auto-Routing?
==================

The previous tutorial introduced you to defined routes manually in **app/Config/Routes.php**. While this is a powerful and flexible way to define your application's routing, it can be tedious for building RESTful APIs where you may have many endpoints that follow a common pattern. Auto-Routing simplifies this by automatically mapping URL patterns to controller classes and methods based on conventions, and the focus on HTTP verbs works quite well for RESTful APIs.

Enable Improved Auto Routing
============================

By default, Auto-Routing is turned off. The first step is to enable it so your controllers automatically handle REST-style methods.

Open ``app/Config/Feature.php`` and ensure this flag is **true** (this should be the default):

.. code-block:: php

    public bool $autoRoutesImproved = true;

The "Improved" auto router is more secure and reliable than the legacy version, so it's recommended for all new projects.

Then, in **app/Config/Routing.php** confirm auto-routing is **enabled**:

.. code-block:: php

    public bool $autoRoute = true;

That's all you need for CodeIgniter to automatically map your controller classes and to URIs like ``GET /api/ping`` or ``POST /api/ping``.

Create a Ping controller
========================

To understand how a basic API endpoint works, let's generate a controller to serve as our first API endpoint. This will provide a simple "ping" response to confirm our setup is correct.

.. code-block:: console

   php spark make:controller Api/Ping

This creates **app/Controllers/Api/Ping.php**.

Edit the file so it looks like this:

.. literalinclude:: code/001.php

Here we:

- Use the :php:class:`ResponseTrait`, which already includes useful REST helpers such as :php:meth:`respond()` and proper status codes.
- Define a ``getIndex()`` method. The **get** prefix means it responds to ``GET`` requests, and the ``Index`` name means that it will match the base URI (``/api/ping``).

Test the route
==============

Start the development server if it isn't running:

.. code-block:: console

   php spark serve

Now visit:

- **Browser:** ``http://localhost:8080/api/ping``
- **cURL:** ``curl http://localhost:8080/api/ping``

Expected response:

.. code-block:: json

    {
        "status": "ok"
    }

Congratulations — that's your first working JSON endpoint!

Understand how it works
=======================

When you request ``/api/ping``:

1. The **Improved Auto Router** finds the ``App\Controllers\Api\Ping`` class.
2. It detects the HTTP verb (``GET``).
3. It calls the corresponding method name: ``getIndex()``.
4. :php:trait:`ResponseTrait` provides helper methods to produce the consistent output.

Here's how other verbs would map if you added them later:

+----------------------+--------------------------------+
| HTTP Verb            | Method Name                    |
+======================+================================+
| ``GET /api/ping``    | ``getIndex()``                 |
| ``POST /api/ping``   | ``postIndex()``                |
| ``DELETE /api/ping`` | ``deleteIndex()``              |
+----------------------+--------------------------------+

Content Negotiation with the Format Class
=========================================

By default, CodeIgniter uses the :php:class:`CodeIgniter\\Format\\Format` class to automatically negotiate the response format. It can return responses in either JSON or XML depending on what the client requests.

The :php:trait:`ResponseTrait` sets the format to JSON by default. You can change this to XML if desired, but this tutorial will focus on JSON responses.

.. literalinclude:: code/002.php

Optional: return more data
==========================

The respond method allows

.. literalinclude:: code/003.php

You now have your first working endpoint and you've managed to test it through both the browser and cURL. In the next section, we'll create our first real database resource. You'll define a **migration**, **seeder**, and **model** for a simple ``books`` table that powers the API's CRUD endpoints.
