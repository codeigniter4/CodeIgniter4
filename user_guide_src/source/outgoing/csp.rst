.. _content-security-policy:

#######################
Content Security Policy
#######################

.. contents::
    :local:
    :depth: 2

********************************
What is Content Security Policy?
********************************

One of the best protections you have against XSS attacks is to implement a Content
Security Policy (CSP) on the site. This requires you to specify and authorize each
source of content that is included in your site's HTML, including images,
stylesheets, JavaScript files, and so on. The browser will reject content from
sources that are not explicitly approved. This authorization is defined within
the response's ``Content-Security-Policy`` header and offers various configuration
options.

This sounds complex, and on some sites, can definitely be challenging. For many simple sites, though, where all content
is served by the same domain (e.g., **http://example.com**), it is very simple to integrate.

As this is a complex subject, this user guide will not go over all of the details. For more information, you should
visit the following sites:

* `Content Security Policy main site <https://content-security-policy.com/>`_
* `W3C Specification <https://www.w3.org/TR/CSP>`_
* `Introduction at HTML5Rocks <https://www.html5rocks.com/en/tutorials/security/content-security-policy/>`_
* `Article at SitePoint <https://www.sitepoint.com/improving-web-security-with-the-content-security-policy/>`_

**************
Turning CSP On
**************

.. important:: The :ref:`Debug Toolbar <the-debug-toolbar>` may use Kint, which
    outputs inline scripts. Therefore, when CSP is turned on, CSP nonce is
    automatically output for the Debug Toolbar. However, if you are not using
    CSP nonce, this will change the CSP header to something you do not intend,
    and it will behave differently than in production; if you want to verify CSP
    behavior, turn off the Debug Toolbar.

By default, support for this is off. To enable support in your application, edit the ``CSPEnabled`` value in
**app/Config/App.php**:

.. literalinclude:: csp/011.php

When enabled, the response object will contain an instance of ``CodeIgniter\HTTP\ContentSecurityPolicy``. The
values set in **app/Config/ContentSecurityPolicy.php** are applied to that instance, and if no changes are
needed during runtime, then the correctly formatted header is sent and you're all done.

With CSP enabled, two header lines are added to the HTTP response: a **Content-Security-Policy** header, with
policies identifying content types or origins that are explicitly allowed for different
contexts, and a **Content-Security-Policy-Report-Only** header, which identifies content types
or origins that will be allowed but which will also be reported to the destination
of your choice.

Our implementation provides for a default treatment, changeable through the ``reportOnly()`` method.
When an additional entry is added to a CSP directive, as shown below, it will be added
to the CSP header appropriate for blocking or preventing. That can be overridden on a per
call basis, by providing an optional second parameter to the adding method call.

*********************
Runtime Configuration
*********************

If your application needs to make changes at run-time, you can access the instance at ``$this->response->getCSP()`` in your controllers.

The
class holds a number of methods that map pretty clearly to the appropriate header value that you need to set.
Examples are shown below, with different combinations of parameters, though all accept either a directive
name or an array of them:

.. literalinclude:: csp/012.php

The first parameter to each of the "add" methods is an appropriate string value,
or an array of them.

Report Only
===========

The ``reportOnly()`` method allows you to specify the default reporting treatment
for subsequent sources, unless over-ridden.

For instance, you could specify
that youtube.com was allowed, and then provide several allowed but reported sources:

.. literalinclude:: csp/013.php

.. _csp-clear-directives:

Clear Directives
================

If you want to clear existing CSP directives, you can use the ``clearDirective()``
method:

.. literalinclude:: csp/014.php

**************
Inline Content
**************

It is possible to set a website to not protect even inline scripts and styles on its own pages, since this might have
been the result of user-generated content. To protect against this, CSP allows you to specify a nonce within the
``<style>`` and ``<script>`` tags, and to add those values to the response's header.

Using Placeholders
==================

This is a pain to handle in real
life, and is most secure when generated on the fly. To make this simple, you can include a ``{csp-style-nonce}`` or
``{csp-script-nonce}`` placeholder in the tag and it will be handled for you automatically::

    // Original
    <script {csp-script-nonce}>
        console.log("Script won't run as it doesn't contain a nonce attribute");
    </script>

    // Becomes
    <script nonce="Eskdikejidojdk978Ad8jf">
        console.log("Script won't run as it doesn't contain a nonce attribute");
    </script>

    // OR
    <style {csp-style-nonce}>
        . . .
    </style>

.. warning:: If an attacker injects a string like ``<script {csp-script-nonce}>``, it might become the real nonce attribute with this functionality. You can customize the placeholder string with the ``$scriptNonceTag`` and ``$styleNonceTag`` properties in **app/Config/ContentSecurityPolicy.php**.

.. _csp-using-functions:

Using Functions
===============

If you don't like the auto replacement functionality above, you can turn it off
with setting ``$autoNonce = false`` in **app/Config/ContentSecurityPolicy.php**.

In this case, you can use the functions, :php:func:`csp_script_nonce()` and :php:func:`csp_style_nonce()`::

    // Original
    <script <?= csp_script_nonce() ?>>
        console.log("Script won't run as it doesn't contain a nonce attribute");
    </script>

    // Becomes
    <script nonce="Eskdikejidojdk978Ad8jf">
        console.log("Script won't run as it doesn't contain a nonce attribute");
    </script>

    // OR
    <style <?= csp_style_nonce() ?>>
        . . .
    </style>
