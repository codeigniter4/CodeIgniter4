##############
AJAX Requests
##############

The ``IncomingRequest::isAJAX()`` method uses the ``X-Requested-With`` header to define whether the request is XHR or normal. However, modern JavaScript APIs such as ``fetch`` no longer send this header by default, so ``IncomingRequest::isAJAX()`` becomes less reliable without additional configuration.

To work around this problem, manually define the request header so the server can identify the request as XHR.

Here are common ways to send the ``X-Requested-With`` header in the Fetch API and other JavaScript libraries.

.. contents::
    :local:
    :depth: 2

Fetch API
=========

.. code-block:: javascript

    fetch(url, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest"
        }
    });

Axios
=====

Axios does not include the ``X-Requested-With`` header by default.
You can add it globally as follows:

.. code-block:: javascript

    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';


If you prefer to avoid global defaults, create an Axios instance instead:

.. code-block:: javascript

    const api = axios.create({
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    });


Vue.js
-------

Vue does not require a specific HTTP client. If your Vue app uses Axios, configure Axios once during application bootstrap or in a shared API module, and reuse that configuration throughout the app.

React
-----

React also does not provide a built-in HTTP client. If your React app uses Axios, reuse the shared Axios configuration above, or set the header for an individual request when needed:

.. code-block:: javascript

    axios.get('your url', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })

jQuery
======

For libraries like jQuery for example, it is not necessary to make explicit the sending of this header, because according to the `official documentation <https://api.jquery.com/jquery.ajax/>`_ it is a standard header for all requests ``$.ajax()``. But if you still want to force the shipment to not take risks, just do it as follows:

.. code-block:: javascript

    $.ajax({
        url: "your url",
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    });

htmx
====

You can use `ajax-header <https://github.com/bigskysoftware/htmx-extensions/blob/main/src/ajax-header/README.md>`_ extension.

.. code-block:: html

    <body hx-ext="ajax-header">
    ...
    </body>


Or you can set the header manually with ``hx-headers``:

.. code-block:: html

    <button
        hx-post="/your-url"
        hx-headers='{"X-Requested-With": "XMLHttpRequest"}'>
        Send Request
    </button>
