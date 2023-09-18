########
Security
########

The Security Class contains methods that help protect your site against Cross-Site Request Forgery attacks.

.. contents::
    :local:
    :depth: 3

*******************
Loading the Library
*******************

If your only interest in loading the library is to handle CSRF protection, then you will never need to load it,
as it runs as a filter and has no manual interaction.

If you find a case where you do need direct access though, you may load it through the Services file:

.. literalinclude:: security/001.php

.. _cross-site-request-forgery:

*********************************
Cross-Site Request Forgery (CSRF)
*********************************

.. warning:: The CSRF Protection is only available for **POST/PUT/PATCH/DELETE** requests.
    Requests for other methods are not protected.

Prerequisite
============

When you use the CodeIgniter's CSRF protection, you still need to code as the following.
Otherwise, the CSRF protection may be bypassed.

When Auto-Routing is Disabled
-----------------------------

Do one of the following:

1. Do not use ``$routes->add()``, and use HTTP verbs in routes.
2. Check the request method in the controller method before processing.

E.g.::

    if (! $this->request->is('post')) {
        return $this->response->setStatusCode(405)->setBody('Method Not Allowed');
    }

.. note:: The :ref:`$this->request->is() <incomingrequest-is>` method can be used since v4.3.0.
    In previous versions, you need to use
    ``if (strtolower($this->request->getMethod()) !== 'post')``.

When Auto-Routing is Enabled
----------------------------

1. Check the request method in the controller method before processing.

E.g.::

    if (! $this->request->is('post')) {
        return $this->response->setStatusCode(405)->setBody('Method Not Allowed');
    }

Config for CSRF
===============

.. _csrf-protection-methods:

CSRF Protection Methods
-----------------------

By default, the Cookie based CSRF Protection is used. It is
`Double Submit Cookie <https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html#double-submit-cookie>`_
on OWASP Cross-Site Request Forgery Prevention Cheat Sheet.

You can also use Session based CSRF Protection. It is
`Synchronizer Token Pattern <https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html#synchronizer-token-pattern>`_.

You can set to use the Session based CSRF protection by editing the following config parameter value in
**app/Config/Security.php**:

.. literalinclude:: security/002.php

Token Randomization
-------------------

To mitigate compression side-channel attacks like `BREACH`_, and prevent an attacker from guessing the CSRF tokens, you can configure token randomization (off by default).

If you enable it, a random mask is added to the token and used to scramble it.

.. _`BREACH`: https://en.wikipedia.org/wiki/BREACH

You can enable it by editing the following config parameter value in
**app/Config/Security.php**:

.. literalinclude:: security/003.php

Token Regeneration
------------------

Tokens may be either regenerated on every submission (default) or
kept the same throughout the life of the CSRF cookie. The default
regeneration of tokens provides stricter security, but may result
in usability concerns as other tokens become invalid (back/forward
navigation, multiple tabs/windows, asynchronous actions, etc). You
may alter this behavior by editing the following config parameter value in
**app/Config/Security.php**:

.. literalinclude:: security/004.php

.. note:: Since v4.2.3, you can regenerate CSRF token manually with the
    ``Security::generateHash()`` method.

.. _csrf-redirection-on-failure:

Redirection on Failure
----------------------

Since v4.3.0, when a request fails the CSRF validation check,
it will throw a SecurityException by default,

.. note:: In production environment, when you use HTML forms, it is recommended
    to enable this redirection for a better user experience.

If you want to make it redirect to the previous page,
change the following config parameter value in
**app/Config/Security.php**:

.. literalinclude:: security/005.php

When redirected, an ``error`` flash message is set and can be displayed to the end user with the following code in your view::

    <?= session()->getFlashdata('error') ?>

This provides a nicer experience than simply crashing.

Even when the redirect value is ``true``, AJAX calls will not redirect, but will throw a SecurityException.

Enable CSRF Protection
======================

You can enable CSRF protection by altering your **app/Config/Filters.php**
and enabling the `csrf` filter globally:

.. literalinclude:: security/006.php

Select URIs can be whitelisted from CSRF protection (for example API
endpoints expecting externally POSTed content). You can add these URIs
by adding them as exceptions in the filter:

.. literalinclude:: security/007.php

Regular expressions are also supported (case-insensitive):

.. literalinclude:: security/008.php

It is also possible to enable the CSRF filter only for specific methods:

.. literalinclude:: security/009.php

.. Warning:: If you use ``$methods`` filters, you should :ref:`disable Auto Routing (Legacy) <use-defined-routes-only>`
    because :ref:`auto-routing-legacy` permits any HTTP method to access a controller.
    Accessing the controller with a method you don't expect could bypass the filter.

HTML Forms
==========

If you use the :doc:`form helper <../helpers/form_helper>`, then
:func:`form_open()` will automatically insert a hidden csrf field in
your forms.

.. note:: To use auto-generation of CSRF field, you need to turn CSRF filter on to the form page.
    In most cases it is requested using the ``GET`` method.

If not, then you can use the always available ``csrf_token()``
and ``csrf_hash()`` functions
::

    <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />

Additionally, you can use the ``csrf_field()`` method to generate this
hidden input field for you::

    // Generates: <input type="hidden" name="{csrf_token}" value="{csrf_hash}" />
    <?= csrf_field() ?>

When sending a JSON request the CSRF token can also be passed as one of the parameters.
The next way to pass the CSRF token is a special Http header that's name is available by
``csrf_header()`` function.

Additionally, you can use the ``csrf_meta()`` method to generate this handy
meta tag for you::

    // Generates: <meta name="{csrf_header}" content="{csrf_hash}" />
    <?= csrf_meta() ?>

The Order of Token Sent by Users
================================

The order of checking the availability of the CSRF token is as follows:

1. ``$_POST`` array
2. HTTP header
3. ``php://input`` (JSON request) - bear in mind that this approach is the slowest one since we have to decode JSON and then re-encode it
4. ``php://input`` (raw body) - for PUT, PATCH, and DELETE type of requests

.. note:: ``php://input`` (raw body) is checked since v4.4.2.

*********************
Other Helpful Methods
*********************

You will never need to use most of the methods in the Security class directly. The following are methods that
you might find helpful that are not related to the CSRF protection.

sanitizeFilename()
==================

Tries to sanitize filenames in order to prevent directory traversal attempts and other security threats, which is
particularly useful for files that were supplied via user input. The first parameter is the path to sanitize.

If it is acceptable for the user input to include relative paths, e.g., **file/in/some/approved/folder.txt**, you can set
the second optional parameter, ``$relativePath`` to ``true``.

.. literalinclude:: security/010.php
